-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 20, 2026 at 08:00 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_cole`
--

-- --------------------------------------------------------

--
-- Table structure for table `becadetalle`
--

CREATE TABLE `becadetalle` (
  `ID` int(11) NOT NULL,
  `IDBECA` int(11) DEFAULT NULL,
  `IDNIVEL` int(11) DEFAULT NULL,
  `IDGRADO` int(11) DEFAULT NULL,
  `ANIOLECTIVO` varchar(50) DEFAULT NULL,
  `IDCATEGORIA` int(11) DEFAULT NULL,
  `IDCONCEPTO` int(11) DEFAULT NULL,
  `PORCENTAJEBECA` int(11) DEFAULT NULL,
  `ESTADO` tinyint(1) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `becamaestro`
--

CREATE TABLE `becamaestro` (
  `ID` int(11) NOT NULL,
  `IDALUMNO` int(11) DEFAULT NULL,
  `AUTORIZADO` varchar(100) DEFAULT NULL,
  `ANIOLECTIVO` varchar(4) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `ESTADO` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conabonos`
--

CREATE TABLE `conabonos` (
  `ID` int(11) NOT NULL,
  `IDCATEGORIA` int(11) DEFAULT NULL,
  `IDCONCEPTO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `IDMenu` int(11) NOT NULL,
  `Descripcion` varchar(50) DEFAULT NULL,
  `URL` varchar(200) DEFAULT NULL,
  `IdPadre` int(11) DEFAULT NULL,
  `Padre` int(11) DEFAULT NULL,
  `Pertenece` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mnurol`
--

CREATE TABLE `mnurol` (
  `IdRol` int(11) NOT NULL,
  `Rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mnurolmenu`
--

CREATE TABLE `mnurolmenu` (
  `Id` int(11) NOT NULL,
  `IdRol` int(11) NOT NULL,
  `IdMenu` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mnuusuarios`
--

CREATE TABLE `mnuusuarios` (
  `IdUsuario` int(11) NOT NULL,
  `Usuario` varchar(50) DEFAULT NULL,
  `Password` varchar(50) DEFAULT NULL,
  `NombreApellido` varchar(50) DEFAULT NULL,
  `Correo` varchar(50) DEFAULT NULL,
  `IdRol` int(11) DEFAULT NULL,
  `FechaCreacion` datetime DEFAULT NULL,
  `UltimoInicioSesion` datetime DEFAULT NULL,
  `UltimaModificacion` datetime DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `placargos`
--

CREATE TABLE `placargos` (
  `ID` int(11) NOT NULL,
  `DESCRIPCION` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plaempleados`
--

CREATE TABLE `plaempleados` (
  `ID` int(11) NOT NULL,
  `NOMBREAPELLIDO` varchar(150) DEFAULT NULL,
  `FECHAREGISTRO` datetime DEFAULT NULL,
  `CARGO` int(11) DEFAULT NULL,
  `DIRECCION` varchar(150) DEFAULT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `MONEDA` varchar(4) DEFAULT NULL,
  `SALARIO` decimal(18,2) DEFAULT NULL,
  `INSS` decimal(18,2) DEFAULT NULL,
  `COMPLEMENTO` decimal(18,2) DEFAULT NULL,
  `TIPOPAGO` int(11) DEFAULT NULL,
  `ESTADO` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `planomina`
--

CREATE TABLE `planomina` (
  `ID` int(11) NOT NULL,
  `MESPAGO` datetime DEFAULT NULL,
  `FECHAPAGOMES` datetime DEFAULT NULL,
  `ESTADO` tinyint(1) DEFAULT 0,
  `FECHA` timestamp NULL DEFAULT current_timestamp(),
  `MONTONOMINACS$` decimal(18,2) DEFAULT NULL,
  `COMPLEMENTOCS$` decimal(18,2) DEFAULT NULL,
  `MONTONOMINAUS$` decimal(18,2) DEFAULT NULL,
  `COMPLEMENTOUS$` decimal(18,2) DEFAULT NULL,
  `TIPOPAGO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `planominadetalle`
--

CREATE TABLE `planominadetalle` (
  `ID` int(11) NOT NULL,
  `IDNOMINA` int(11) DEFAULT NULL,
  `IDEMPLEADO` int(11) DEFAULT NULL,
  `SALARIO` decimal(18,2) DEFAULT NULL,
  `COMPLEMENTO` decimal(18,2) DEFAULT NULL,
  `FECHA` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plaprestamo`
--

CREATE TABLE `plaprestamo` (
  `ID` int(11) NOT NULL,
  `FECHAPRESTAMO` datetime DEFAULT NULL,
  `FECHACORTE1` datetime DEFAULT NULL,
  `FECHACORTE2` datetime DEFAULT NULL,
  `IDEMPLEADO` int(11) DEFAULT NULL,
  `CANTIDADCUOTAS` int(11) DEFAULT NULL,
  `TIPOPAGO` int(11) DEFAULT NULL,
  `MONTO` decimal(18,2) DEFAULT NULL,
  `SALDO` decimal(18,2) DEFAULT NULL,
  `ESTADO` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plaprestamosdetalle`
--

CREATE TABLE `plaprestamosdetalle` (
  `ID` int(11) NOT NULL,
  `IDPRESTAMO` int(11) DEFAULT NULL,
  `FECHAPAGO` datetime DEFAULT NULL,
  `CUOTA` decimal(18,2) DEFAULT NULL,
  `PAGADO` tinyint(1) DEFAULT 0,
  `FECHACOBRO` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `platipopago`
--

CREATE TABLE `platipopago` (
  `ID` int(11) NOT NULL,
  `DESCRIPCION` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasa_cambio`
--

CREATE TABLE `tasa_cambio` (
  `id_tasa` int(11) UNSIGNED NOT NULL,
  `tasa` float NOT NULL,
  `fecha_tasa` date NOT NULL,
  `activo_tasa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tasa_cambio`
--

INSERT INTO `tasa_cambio` (`id_tasa`, `tasa`, `fecha_tasa`, `activo_tasa`) VALUES
(1, 37, '2025-01-04', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbla_email`
--

CREATE TABLE `tbla_email` (
  `id_email` int(11) UNSIGNED NOT NULL,
  `host` varchar(200) NOT NULL,
  `port` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `mensaje` varchar(1600) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tbla_email`
--

INSERT INTO `tbla_email` (`id_email`, `host`, `port`, `username`, `password`, `subject`, `mensaje`) VALUES
(1, 'mail.netsoluciones.com', '587', 'notificaciones@iparcoiris.com', 'WrLnexe91', 'Notificacion de pagos en mora', 'Estimado padre/ madre de familia. Les recordamos que las fechas estipuladas para pago de aranceles son los primeros 10 días de cada mes. Por medio del presente los invitamos a ponerse al día a más tardar el día de mañana en nuestras oficinas o bien por medio de transferencia. Si usted ya canceló favor enviar comprobante por correo o whatsapp para que podamos hacer la cancelación en el sistema. Muchas gracias.  ');

-- --------------------------------------------------------

--
-- Table structure for table `tbla_usuario`
--

CREATE TABLE `tbla_usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `apellido_usuario` varchar(50) NOT NULL,
  `email_usuario` varchar(50) NOT NULL,
  `telefono` varchar(100) NOT NULL,
  `password_usuario` varchar(255) NOT NULL,
  `tipo_usuario` int(11) NOT NULL,
  `activo_usuario` int(11) NOT NULL,
  `fecha_r_usuario` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tbla_usuario`
--

INSERT INTO `tbla_usuario` (`id_usuario`, `nombre_usuario`, `apellido_usuario`, `email_usuario`, `telefono`, `password_usuario`, `tipo_usuario`, `activo_usuario`, `fecha_r_usuario`) VALUES
(1, 'admin', 'admin', 'admin@hotmail.es', '', '$2y$12$P/dlpQfYVEhZeP7jE9IEauGb3/mCI2tzA8y./Gz9R3r/p56bxBK.i', 1, 1, '2023-12-08 00:00:00'),
(2, 'gerencia', 'gerencia', 'gerencia@hotmail.es', '75038506', '$2y$12$P/dlpQfYVEhZeP7jE9IEauGb3/mCI2tzA8y./Gz9R3r/p56bxBK.i', 2, 1, '2023-12-15 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_alumnos`
--

CREATE TABLE `tbl_alumnos` (
  `IDALUMNO` int(11) NOT NULL,
  `NOMBREAPELLIDO` varchar(255) DEFAULT NULL,
  `CODIGO` varchar(25) DEFAULT NULL,
  `FECHANACIMIENTO` datetime DEFAULT NULL,
  `NOMBREMADRE` varchar(150) DEFAULT NULL,
  `NOMBREPADRE` varchar(150) DEFAULT NULL,
  `DIRECCION` varchar(150) DEFAULT NULL,
  `TELEFONO` varchar(20) DEFAULT NULL,
  `EMAIL` varchar(250) NOT NULL,
  `GENERO` varchar(30) NOT NULL DEFAULT '',
  `FOTO` varchar(200) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_anios`
--

CREATE TABLE `tbl_anios` (
  `idanio` int(11) NOT NULL,
  `anio` varchar(4) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_anios`
--

INSERT INTO `tbl_anios` (`idanio`, `anio`, `estado`) VALUES
(1, '2013', 0),
(2, '2014', 0),
(3, '2015', 0),
(4, '2016', 0),
(5, '2017', 0),
(6, '2018', 0),
(7, '2019', 0),
(8, '2020', 0),
(9, '2021', 0),
(10, '2022', 0),
(11, '2023', 0),
(12, '2024', 0),
(13, '2025', 0),
(14, '2026', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_aranceles`
--

CREATE TABLE `tbl_aranceles` (
  `ID` int(11) NOT NULL,
  `IdCategoria` int(11) DEFAULT NULL,
  `IdConcepto` int(11) DEFAULT NULL,
  `IdNivel` int(11) DEFAULT NULL,
  `TipoMoneda` varchar(4) DEFAULT NULL,
  `Monto` decimal(18,2) DEFAULT NULL,
  `Anio` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cargos`
--

CREATE TABLE `tbl_cargos` (
  `Id` int(11) NOT NULL,
  `IdAlumno` int(11) DEFAULT NULL,
  `AnioLectivo` varchar(4) DEFAULT NULL,
  `IdNivel` int(11) DEFAULT NULL,
  `IdGrado` int(11) DEFAULT NULL,
  `IdSeccion` int(11) DEFAULT NULL,
  `IdConcepto` int(11) DEFAULT NULL,
  `MesReferencia` datetime DEFAULT NULL,
  `MontoGenerado` decimal(18,2) DEFAULT NULL,
  `Saldo` decimal(18,2) DEFAULT NULL,
  `FechaCreacion` timestamp NULL DEFAULT current_timestamp(),
  `FechaModificacion` datetime DEFAULT NULL,
  `FechaCorte` datetime DEFAULT NULL,
  `Anulado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_categoriapago`
--

CREATE TABLE `tbl_categoriapago` (
  `Id` int(11) NOT NULL,
  `Concepto` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_conceptospago`
--

CREATE TABLE `tbl_conceptospago` (
  `IdConcepto` int(11) NOT NULL,
  `IdCategoria` int(11) DEFAULT NULL,
  `Concepto` varchar(50) DEFAULT NULL,
  `IE` varchar(2) DEFAULT NULL,
  `MONTO` varchar(2) DEFAULT NULL,
  `unidades` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_examenes`
--

CREATE TABLE `tbl_examenes` (
  `Id` int(11) NOT NULL,
  `Examen` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_grados`
--

CREATE TABLE `tbl_grados` (
  `IdGrados` int(11) NOT NULL,
  `Grado` varchar(30) NOT NULL,
  `Nivel` varchar(30) DEFAULT NULL,
  `IdNivel` int(11) DEFAULT NULL,
  `total_cupo` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_horarioescolar`
--

CREATE TABLE `tbl_horarioescolar` (
  `Id` int(11) NOT NULL,
  `IdAnio` int(11) DEFAULT NULL,
  `HoraInicio` varchar(10) DEFAULT NULL,
  `HoraFin` varchar(10) DEFAULT NULL,
  `IdGrado` int(11) DEFAULT NULL,
  `Seccion` varchar(1) DEFAULT NULL,
  `IdMateria` int(11) DEFAULT NULL,
  `Dias` varchar(15) DEFAULT NULL,
  `IdMaestro` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_horas`
--

CREATE TABLE `tbl_horas` (
  `ID` int(11) NOT NULL,
  `HORA` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_inscripcion`
--

CREATE TABLE `tbl_inscripcion` (
  `ID` int(11) NOT NULL,
  `IDMATRICULA` int(11) DEFAULT NULL,
  `IDALUMNO` int(11) DEFAULT NULL,
  `IDNIVEL` int(11) DEFAULT NULL,
  `IDGRADO` int(11) DEFAULT NULL,
  `SECCION` varchar(100) DEFAULT NULL,
  `ANIOLECTIVO` varchar(4) DEFAULT NULL,
  `BECA` varchar(2) DEFAULT NULL,
  `PORCENTAJEBECA` int(11) DEFAULT NULL,
  `IDTURNO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_materias`
--

CREATE TABLE `tbl_materias` (
  `Id` int(11) NOT NULL,
  `Materia` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_matricula`
--

CREATE TABLE `tbl_matricula` (
  `ID` int(11) NOT NULL,
  `IDALUMNO` int(11) DEFAULT NULL,
  `ANIO` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mes`
--

CREATE TABLE `tbl_mes` (
  `ID` int(11) NOT NULL,
  `MES` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_mes`
--

INSERT INTO `tbl_mes` (`ID`, `MES`) VALUES
(1, 'ENERO'),
(2, 'FEBRERO'),
(3, 'MARZO'),
(4, 'ABRIL'),
(5, 'MAYO'),
(6, 'JUNIO'),
(7, 'JULIO'),
(8, 'AGOSTO'),
(9, 'SEPTIEMBRE'),
(10, 'OCTUBRE'),
(11, 'NOVIEMBRE'),
(12, 'DICIEMBRE');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_nivel`
--

CREATE TABLE `tbl_nivel` (
  `IdNivel` int(11) NOT NULL,
  `Nivel` char(15) NOT NULL,
  `Mensualidad` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notas`
--

CREATE TABLE `tbl_notas` (
  `Id` int(11) NOT NULL,
  `IdAnio` int(11) DEFAULT NULL,
  `IdInscripcion` int(11) DEFAULT NULL,
  `IdOferta` int(11) DEFAULT NULL,
  `IParcial` int(11) DEFAULT NULL,
  `IIParcial` int(11) DEFAULT NULL,
  `IIIParcial` int(11) DEFAULT NULL,
  `IVParcial` int(11) DEFAULT NULL,
  `NotaFinal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_numeracionfactura`
--

CREATE TABLE `tbl_numeracionfactura` (
  `Numero` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_numeracionfactura`
--

INSERT INTO `tbl_numeracionfactura` (`Numero`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ofertaacademica`
--

CREATE TABLE `tbl_ofertaacademica` (
  `ID` int(11) NOT NULL,
  `IDPROFESOR` int(11) NOT NULL,
  `IDMATERIA` int(11) NOT NULL,
  `IDGRADO` int(11) NOT NULL,
  `SECCION` int(11) NOT NULL,
  `IDANIO` int(11) NOT NULL,
  `RESPONSABLE` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pagosmensualidades`
--

CREATE TABLE `tbl_pagosmensualidades` (
  `Id` int(11) NOT NULL,
  `IdInscripcion` int(11) NOT NULL,
  `Ene` char(1) DEFAULT NULL,
  `Feb` char(1) DEFAULT NULL,
  `Mar` char(1) DEFAULT NULL,
  `Abr` char(1) DEFAULT NULL,
  `May` char(1) DEFAULT NULL,
  `Jun` char(1) DEFAULT NULL,
  `Jul` char(1) DEFAULT NULL,
  `Ago` char(1) DEFAULT NULL,
  `Sep` char(1) DEFAULT NULL,
  `Oct` char(1) DEFAULT NULL,
  `Nov` char(1) DEFAULT NULL,
  `Dic` char(1) DEFAULT NULL,
  `Anio` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_parametros`
--

CREATE TABLE `tbl_parametros` (
  `NOMBRECOLEGIO` varchar(150) DEFAULT NULL,
  `LEMA` varchar(150) DEFAULT NULL,
  `DIRECCION` varchar(150) DEFAULT NULL,
  `TELEFONOS` varchar(50) DEFAULT NULL,
  `logo_colegio` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_parametros`
--

INSERT INTO `tbl_parametros` (`NOMBRECOLEGIO`, `LEMA`, `DIRECCION`, `TELEFONOS`, `logo_colegio`) VALUES
('INSTITUTO PEDAGOGICO ARCOIRIS', 'AÑO LECTIVO 2015', 'Direccion del Colegio', '9999 8888', 'uploads/Screenshot_2026-03-06_at_10_10_50___AM_400x400_20260306_211914_89a631.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_profesores`
--

CREATE TABLE `tbl_profesores` (
  `Id` int(11) NOT NULL,
  `Nombre` varchar(100) DEFAULT NULL,
  `Telefono` varchar(10) DEFAULT NULL,
  `Celular` varchar(10) DEFAULT NULL,
  `Direccion` varchar(150) DEFAULT NULL,
  `SALARIO` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_recibo`
--

CREATE TABLE `tbl_recibo` (
  `IdRecibo` int(11) NOT NULL,
  `IdReciboMaestro` int(11) DEFAULT NULL,
  `FechaPago` datetime DEFAULT NULL,
  `MesReferencia` datetime DEFAULT NULL,
  `Anio` varchar(4) DEFAULT NULL,
  `IdAlumno` int(11) DEFAULT NULL,
  `NombreApellido` varchar(50) DEFAULT NULL,
  `Nivel` varchar(50) DEFAULT NULL,
  `Grado` varchar(50) DEFAULT NULL,
  `SECCION` varchar(2) DEFAULT NULL,
  `TipodePago` int(11) DEFAULT NULL,
  `IdConcepto` int(11) DEFAULT NULL,
  `Concepto` varchar(50) DEFAULT NULL,
  `IdCategoriaConcepto` int(11) DEFAULT NULL,
  `NombreCategoria` varchar(50) DEFAULT NULL,
  `TotalConcepto` decimal(18,2) DEFAULT NULL,
  `Mora` decimal(18,2) DEFAULT NULL,
  `subtotal` decimal(18,2) DEFAULT NULL,
  `Abono` decimal(18,2) DEFAULT NULL,
  `Saldo` decimal(18,2) DEFAULT NULL,
  `ValorTotal` decimal(18,2) DEFAULT NULL,
  `Beca` varchar(2) DEFAULT NULL,
  `Anulado` tinyint(1) DEFAULT NULL,
  `IE` varchar(1) DEFAULT NULL,
  `Observaciones` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_recibomaestro`
--

CREATE TABLE `tbl_recibomaestro` (
  `Id` int(11) NOT NULL,
  `Fecha` datetime NOT NULL,
  `Estado` tinyint(1) DEFAULT NULL,
  `MontoTotal` decimal(18,2) DEFAULT NULL,
  `IdAlumno` int(11) DEFAULT NULL,
  `Observaciones` varchar(500) DEFAULT NULL,
  `Cajero` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_secciones`
--

CREATE TABLE `tbl_secciones` (
  `ID` int(11) NOT NULL,
  `SECCION` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_turno`
--

CREATE TABLE `tbl_turno` (
  `ID` int(11) NOT NULL,
  `Turno` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `becadetalle`
--
ALTER TABLE `becadetalle`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `becamaestro`
--
ALTER TABLE `becamaestro`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `conabonos`
--
ALTER TABLE `conabonos`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`IDMenu`);

--
-- Indexes for table `mnurol`
--
ALTER TABLE `mnurol`
  ADD PRIMARY KEY (`IdRol`);

--
-- Indexes for table `mnurolmenu`
--
ALTER TABLE `mnurolmenu`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `mnuusuarios`
--
ALTER TABLE `mnuusuarios`
  ADD PRIMARY KEY (`IdUsuario`);

--
-- Indexes for table `placargos`
--
ALTER TABLE `placargos`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `plaempleados`
--
ALTER TABLE `plaempleados`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `planomina`
--
ALTER TABLE `planomina`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `planominadetalle`
--
ALTER TABLE `planominadetalle`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `plaprestamo`
--
ALTER TABLE `plaprestamo`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `plaprestamosdetalle`
--
ALTER TABLE `plaprestamosdetalle`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `platipopago`
--
ALTER TABLE `platipopago`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tasa_cambio`
--
ALTER TABLE `tasa_cambio`
  ADD PRIMARY KEY (`id_tasa`);

--
-- Indexes for table `tbla_email`
--
ALTER TABLE `tbla_email`
  ADD PRIMARY KEY (`id_email`);

--
-- Indexes for table `tbla_usuario`
--
ALTER TABLE `tbla_usuario`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indexes for table `tbl_alumnos`
--
ALTER TABLE `tbl_alumnos`
  ADD PRIMARY KEY (`IDALUMNO`);

--
-- Indexes for table `tbl_anios`
--
ALTER TABLE `tbl_anios`
  ADD PRIMARY KEY (`idanio`);

--
-- Indexes for table `tbl_aranceles`
--
ALTER TABLE `tbl_aranceles`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_cargos`
--
ALTER TABLE `tbl_cargos`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_categoriapago`
--
ALTER TABLE `tbl_categoriapago`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_conceptospago`
--
ALTER TABLE `tbl_conceptospago`
  ADD PRIMARY KEY (`IdConcepto`);

--
-- Indexes for table `tbl_examenes`
--
ALTER TABLE `tbl_examenes`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_grados`
--
ALTER TABLE `tbl_grados`
  ADD PRIMARY KEY (`IdGrados`);

--
-- Indexes for table `tbl_horarioescolar`
--
ALTER TABLE `tbl_horarioescolar`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_horas`
--
ALTER TABLE `tbl_horas`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_inscripcion`
--
ALTER TABLE `tbl_inscripcion`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_materias`
--
ALTER TABLE `tbl_materias`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_matricula`
--
ALTER TABLE `tbl_matricula`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_mes`
--
ALTER TABLE `tbl_mes`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_nivel`
--
ALTER TABLE `tbl_nivel`
  ADD PRIMARY KEY (`IdNivel`);

--
-- Indexes for table `tbl_notas`
--
ALTER TABLE `tbl_notas`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_ofertaacademica`
--
ALTER TABLE `tbl_ofertaacademica`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_pagosmensualidades`
--
ALTER TABLE `tbl_pagosmensualidades`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_profesores`
--
ALTER TABLE `tbl_profesores`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_recibo`
--
ALTER TABLE `tbl_recibo`
  ADD PRIMARY KEY (`IdRecibo`);

--
-- Indexes for table `tbl_recibomaestro`
--
ALTER TABLE `tbl_recibomaestro`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbl_secciones`
--
ALTER TABLE `tbl_secciones`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_turno`
--
ALTER TABLE `tbl_turno`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `becadetalle`
--
ALTER TABLE `becadetalle`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `becamaestro`
--
ALTER TABLE `becamaestro`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conabonos`
--
ALTER TABLE `conabonos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `IDMenu` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mnurol`
--
ALTER TABLE `mnurol`
  MODIFY `IdRol` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mnurolmenu`
--
ALTER TABLE `mnurolmenu`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mnuusuarios`
--
ALTER TABLE `mnuusuarios`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `placargos`
--
ALTER TABLE `placargos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plaempleados`
--
ALTER TABLE `plaempleados`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `planomina`
--
ALTER TABLE `planomina`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `planominadetalle`
--
ALTER TABLE `planominadetalle`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plaprestamo`
--
ALTER TABLE `plaprestamo`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plaprestamosdetalle`
--
ALTER TABLE `plaprestamosdetalle`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platipopago`
--
ALTER TABLE `platipopago`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasa_cambio`
--
ALTER TABLE `tasa_cambio`
  MODIFY `id_tasa` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbla_email`
--
ALTER TABLE `tbla_email`
  MODIFY `id_email` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbla_usuario`
--
ALTER TABLE `tbla_usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_alumnos`
--
ALTER TABLE `tbl_alumnos`
  MODIFY `IDALUMNO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_anios`
--
ALTER TABLE `tbl_anios`
  MODIFY `idanio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_aranceles`
--
ALTER TABLE `tbl_aranceles`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cargos`
--
ALTER TABLE `tbl_cargos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_categoriapago`
--
ALTER TABLE `tbl_categoriapago`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_conceptospago`
--
ALTER TABLE `tbl_conceptospago`
  MODIFY `IdConcepto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_examenes`
--
ALTER TABLE `tbl_examenes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_grados`
--
ALTER TABLE `tbl_grados`
  MODIFY `IdGrados` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_horarioescolar`
--
ALTER TABLE `tbl_horarioescolar`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_horas`
--
ALTER TABLE `tbl_horas`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_inscripcion`
--
ALTER TABLE `tbl_inscripcion`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_materias`
--
ALTER TABLE `tbl_materias`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_matricula`
--
ALTER TABLE `tbl_matricula`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_mes`
--
ALTER TABLE `tbl_mes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_nivel`
--
ALTER TABLE `tbl_nivel`
  MODIFY `IdNivel` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_notas`
--
ALTER TABLE `tbl_notas`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_ofertaacademica`
--
ALTER TABLE `tbl_ofertaacademica`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_pagosmensualidades`
--
ALTER TABLE `tbl_pagosmensualidades`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_profesores`
--
ALTER TABLE `tbl_profesores`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_recibo`
--
ALTER TABLE `tbl_recibo`
  MODIFY `IdRecibo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_recibomaestro`
--
ALTER TABLE `tbl_recibomaestro`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_secciones`
--
ALTER TABLE `tbl_secciones`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_turno`
--
ALTER TABLE `tbl_turno`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
