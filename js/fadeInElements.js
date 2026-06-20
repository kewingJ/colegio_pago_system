/* 
	Author: Ryan H
	Repo: https://github.com/Ryan8765/Fade-in-Elements-Scroll
*/

(function ( $ ) {
	var fadeInElementsObject =  {
		alreadyFaded: [],
		//when scroll position hits bottom only trigger fadeAll once
		fadeAllTriggered: "no",
	};

    $.fn.fadeInElements = function(options) {
    	//default settings
    	var settings = $.extend({
            fadeDuration: 2000,
            fadePosition: 0
        }, options );
    	var elements = this;
        var fadeElements = {
        	//fadePosition changes the start of when an element will fade in
        	fadePosition: (window.innerHeight) * (settings.fadePosition / 100 ),
			//length of fade
			fadeTime: settings.fadeDuration,
			//elements to fade in
			elementsToFadeIn: elements,
			initialLength: elements.length,
			fadeStuff: function() {
				//max index of elements already faded in
				var maxIndexAlreadyFaded = (Math.max.apply(null, fadeInElementsObject.alreadyFaded));			
				//current element to look for distance from top of document
				var currentElement;
				//distance from top of element to top of document
				var distanceToTop;
				//scroll position
				var scrollPosition;
				//if all of the fadein elements aren't showing yet check for them on scroll..otherwise bypass this to save computing
				if (fadeInElementsObject.alreadyFaded.length < this.initialLength) {
					//for loop to go through all of elements and see if they are within view
					for (var i = 0; i < this.initialLength; i++) {
						//if you have already shown element continue on 
						if (fadeInElementsObject.alreadyFaded.indexOf(i) > -1) continue;
						//grab the first element in array
						currentElement = this.elementsToFadeIn.eq(i);
						//get distance from top of element to top of document
						distanceToTop = currentElement.offset().top;
						//current scroll position
						scrollPosition = $(window).scrollTop();
						//if scroll position is greater than distance to top fadein element
						if (distanceToTop < scrollPosition + window.innerHeight - this.fadePosition) {
							currentElement.fadeTo(this.fadeTime, 1);
							fadeInElementsObject.alreadyFaded.push(i);
						}//end if
					}//end for
					//if you hit bottom of page on scroll make sure to fade in remaining elements all the way to the end of the document.
					if (scrollPosition + window.innerHeight > $(document).height() - 10 && fadeInElementsObject.fadeAllTriggered === "no") {
						//find elements not yet faded in and fade them in
						for(var i = maxIndexAlreadyFaded; i < this.initialLength; i++) {
							elements.eq(i).fadeTo(this.fadeTime, 1);
						}//end for
						fadeInElementsObject.fadeAllTriggered = "yes";
					}//end if	
				}//end if
			}//end fadestuff function
		};//end fadeelements Object
		fadeElements.fadeStuff();
		$(window).scroll(function() {
			fadeElements.fadeStuff();
		});
    };//end fadeinelements function
}( jQuery ));