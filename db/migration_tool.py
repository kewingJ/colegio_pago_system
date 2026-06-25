import re
import os

def get_full_schema(filepaths):
    schema = {}
    table_defs = {}

    for filepath in filepaths:
        if not os.path.exists(filepath): continue
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()

        # Capture both standard CREATE TABLE and those with IF NOT EXISTS
        matches = re.finditer(r'CREATE TABLE (?:IF NOT EXISTS )?`(\w+)` \((.*?)\) ENGINE', content, re.DOTALL)
        for match in matches:
            table_name = match.group(1)
            body = match.group(2)
            table_defs[table_name] = match.group(0)

            cols = []
            for line in body.split('\n'):
                line = line.strip()
                if line.startswith('`'):
                    col_match = re.match(r'`([^`]+)`', line)
                    if col_match:
                        cols.append(col_match.group(1))
            schema[table_name] = cols

    return schema, table_defs

def extract_inserts(filepath):
    inserts = {}
    if not os.path.exists(filepath): return inserts

    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        current_table = None
        current_values = []

        for line in f:
            match = re.match(r'INSERT INTO `(\w+)` (?:VALUES|\(.*?\)\s*VALUES)\s*(.*)', line, re.IGNORECASE)
            if match:
                current_table = match.group(1)
                if current_table not in inserts:
                    inserts[current_table] = []

                val_part = match.group(2).strip()
                if val_part.endswith(';'):
                    current_values.append(val_part[:-1])
                    process_values(current_table, "".join(current_values), inserts)
                    current_table = None
                    current_values = []
                else:
                    current_values.append(val_part)
            elif current_table:
                val_part = line.strip()
                if val_part.endswith(';'):
                    current_values.append(val_part[:-1])
                    process_values(current_table, "".join(current_values), inserts)
                    current_table = None
                    current_values = []
                else:
                    current_values.append(val_part)

    return inserts

def process_values(table_name, values_str, inserts):
    rows = []
    current_row = ""
    paren_count = 0
    in_string = False
    escape = False

    for char in values_str:
        if char == "'" and not escape:
            in_string = not in_string

        if char == "\\" and not escape:
            escape = True
        else:
            escape = False

        if not in_string:
            if char == '(':
                paren_count += 1
            elif char == ')':
                paren_count -= 1

        current_row += char

        if paren_count == 0 and char == ',' and not in_string:
            row = current_row.strip().strip(',').strip()
            if row:
                rows.append(row)
            current_row = ""

    if current_row.strip():
        row = current_row.strip().strip(',').strip()
        if row:
            rows.append(row)

    inserts[table_name].extend(rows)

def parse_row(row_str):
    row_str = row_str.strip()
    if row_str.startswith('(') and row_str.endswith(')'):
        row_str = row_str[1:-1]

    values = []
    current_val = ""
    in_string = False
    escape = False

    for char in row_str:
        if char == "'" and not escape:
            in_string = not in_string

        if char == "\\" and not escape:
            escape = True
        else:
            escape = False

        if char == ',' and not in_string:
            values.append(current_val.strip())
            current_val = ""
        else:
            current_val += char
    values.append(current_val.strip())
    return values

def main():
    print("Extracting target schema...")
    target_schema, target_table_defs = get_full_schema(['db/db_cole.sql'])

    # Explicitly look for IF NOT EXISTS tables in optimizations.sql and api_update.sql
    for opt_file in ['db/optimizations.sql', 'db/api_update.sql']:
        with open(opt_file, 'r') as f:
            content = f.read()
            matches = re.finditer(r'CREATE TABLE IF NOT EXISTS `(\w+)` \((.*?)\) ENGINE', content, re.DOTALL)
            for match in matches:
                table_name = match.group(1)
                body = match.group(2)
                target_table_defs[table_name] = match.group(0)

                cols = []
                for line in body.split('\n'):
                    line = line.strip()
                    if line.startswith('`'):
                        col_match = re.match(r'`([^`]+)`', line)
                        if col_match:
                            cols.append(col_match.group(1))
                target_schema[table_name] = cols

    print("Extracting data from backup...")
    backup_inserts = extract_inserts('db/respaldo_2026-06-23_23-21-25.sql')
    print(f"Found data for {len(backup_inserts)} tables in backup.")

    print("Extracting data from db_cole.sql...")
    test_inserts = extract_inserts('db/db_cole.sql')
    print(f"Found data for {len(test_inserts)} tables in db_cole.sql.")

    all_tables = sorted(target_schema.keys())

    with open('db/db_final_completa.sql', 'w', encoding='utf-8') as out:
        out.write("SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\nSTART TRANSACTION;\nSET time_zone = \"+00:00\";\n\n")

        for table in all_tables:
            out.write(f"-- Table structure for table `{table}`\n")
            out.write(f"DROP TABLE IF EXISTS `{table}`;\n")
            t_def = target_table_defs[table]
            # Replace IF NOT EXISTS if it was captured
            t_def = t_def.replace("IF NOT EXISTS ", "")
            if "ENGINE=" not in t_def:
                t_def += " ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            out.write(t_def + ";\n\n")

        for table in all_tables:
            rows_to_insert = []
            combined_raw_rows = []
            if table in backup_inserts:
                combined_raw_rows.extend([(row, 'backup') for row in backup_inserts[table]])
            if table in test_inserts:
                combined_raw_rows.extend([(row, 'test') for row in test_inserts[table]])

            if not combined_raw_rows:
                continue

            print(f"Migrating data for {table} ({len(combined_raw_rows)} rows)...")

            target_cols = target_schema[table]

            for row_str, source_type in combined_raw_rows:
                parsed_vals = parse_row(row_str)

                if len(parsed_vals) == len(target_cols):
                    rows_to_insert.append(row_str)
                elif len(parsed_vals) < len(target_cols):
                    if table == 'tbl_alumnos' and len(parsed_vals) == 9:
                        parsed_vals.extend(["''", "''"])
                        rows_to_insert.append("(" + ",".join(parsed_vals) + ")")
                    elif table == 'tbl_conceptospago' and len(parsed_vals) == 5:
                        parsed_vals.append("0")
                        rows_to_insert.append("(" + ",".join(parsed_vals) + ")")
                    elif table == 'tbl_grados' and len(parsed_vals) == 4:
                        parsed_vals.append("0")
                        rows_to_insert.append("(" + ",".join(parsed_vals) + ")")
                    elif table == 'tbl_parametros' and len(parsed_vals) == 4:
                        parsed_vals.append("NULL")
                        rows_to_insert.append("(" + ",".join(parsed_vals) + ")")
                    elif table == 'tbla_usuario' and len(parsed_vals) == 8:
                        new_vals = parsed_vals[:4] + ["''"] + parsed_vals[4:]
                        rows_to_insert.append("(" + ",".join(new_vals) + ")")
                    else:
                        while len(parsed_vals) < len(target_cols):
                            parsed_vals.append("NULL")
                        rows_to_insert.append("(" + ",".join(parsed_vals) + ")")
                else:
                    rows_to_insert.append("(" + ",".join(parsed_vals[:len(target_cols)]) + ")")

            if rows_to_insert:
                chunk_size = 100
                for i in range(0, len(rows_to_insert), chunk_size):
                    chunk = rows_to_insert[i:i+chunk_size]
                    out.write(f"INSERT INTO `{table}` VALUES\n")
                    out.write(",\n".join(chunk) + ";\n")
                out.write("\n")

        print("Adding optimizations (indexes)...")
        if os.path.exists('db/optimizations.sql'):
            with open('db/optimizations.sql', 'r') as f:
                opt_content = f.read()
                alters = re.findall(r'ALTER TABLE.*?;', opt_content, re.DOTALL)
                for alter in alters:
                    if "ADD INDEX" in alter or "ADD CONSTRAINT" in alter:
                        out.write(alter + "\n")

        print("Adding PKs and AutoIncrements...")
        with open('db/db_cole.sql', 'r') as f:
            cole_content = f.read()
            pks = re.findall(r'ALTER TABLE `\w+` ADD PRIMARY KEY \(.*?\);', cole_content, re.DOTALL)
            for pk in pks:
                out.write(pk + "\n")

            ais = re.findall(r'ALTER TABLE `\w+` MODIFY `\w+` .*? AUTO_INCREMENT.*?;', cole_content, re.DOTALL)
            for ai in ais:
                out.write(ai + "\n")

        out.write("\nCOMMIT;\n")

if __name__ == "__main__":
    main()
