//DOM
function navigateBreadcrumbBack() {

    const breadcrumbs = Array.from(document.querySelectorAll("#LargeTopContainer .breadcrumbmain"));
    const visibleBreadcrumbs = breadcrumbs.filter(el => getComputedStyle(el).display !== "none");

    if (visibleBreadcrumbs.length > 0) {
        visibleBreadcrumbs[visibleBreadcrumbs.length - 1].click();
    }
}

//DOM - set highlighting
function highlight_DataElements(color = 'gray') {

	document.querySelectorAll('.treedata').forEach(element =>
	{
		element.style.backgroundColor = color;
	});
}
function highlight_Text(element, color = 'yellow') {

	element.style.backgroundImage = "linear-gradient(to right, " + color + " 50%, " + color + " 50%)";
	element.style.backgroundSize = "100% 60%"; // Controls thickness
	element.style.backgroundPosition = "0 85%"; // Moves it below text
	element.style.backgroundRepeat = "no-repeat";
}
function highlight_TextPop(element, color = 'yellow') {

    // Mouse enter: apply highlight
    element.addEventListener("mouseenter", () => {
        element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
        element.style.backgroundSize = "100% 60%"; // Highlight thickness
        element.style.backgroundPosition = "0 85%"; // Position below text
        element.style.backgroundRepeat = "no-repeat";
    });

    // Mouse leave: remove highlight
    element.addEventListener("mouseleave", () => {
        element.style.backgroundImage = "none"; // Remove highlight
    });

    // Mouse click: blink the highlight
    element.addEventListener("click", () => {

		// Remove the highlight briefly
        element.style.backgroundImage = "none";

        // Add the highlight back after 500ms (you can adjust this time)
        setTimeout(() => {
            element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
            element.style.backgroundSize = "100% 60%";
            element.style.backgroundPosition = "0 85%";
            element.style.backgroundRepeat = "no-repeat";
        }, 150);  // 150ms delay (adjustable)
    });
}
function highlight_TextPop_WithEvents(element, color = 'yellow') {

    // Create event handler functions
    const mouseEnterHandler = () => {
        element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
        element.style.backgroundSize = "100% 60%"; // Highlight thickness
        element.style.backgroundPosition = "0 85%"; // Position below text
        element.style.backgroundRepeat = "no-repeat";
    };

    const mouseLeaveHandler = () => {
        element.style.backgroundImage = "none"; // Remove highlight
    };

    const clickHandler = () => {
        element.style.backgroundImage = "none";  // Remove the highlight briefly
        setTimeout(() => {
            element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
            element.style.backgroundSize = "100% 60%";
            element.style.backgroundPosition = "0 85%";
            element.style.backgroundRepeat = "no-repeat";
        }, 150);  // 150ms delay
    };

    // Add event listeners
    element.addEventListener("mouseenter", mouseEnterHandler);
    element.addEventListener("mouseleave", mouseLeaveHandler);
    element.addEventListener("click", clickHandler);

    // To remove the event listeners when necessary:
    element.removeEventListeners = () => {
        element.removeEventListener("mouseenter", mouseEnterHandler);
        element.removeEventListener("mouseleave", mouseLeaveHandler);
        element.removeEventListener("click", clickHandler);
    };
}
function highlight_TextFadeTopBottom(element, color = 'yellow') {

	element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
    element.style.backgroundSize = "100% 0%"; // Start with no highlight
    element.style.backgroundPosition = "0 85%"; // Below text
    element.style.backgroundRepeat = "no-repeat";
    element.style.transition = "background-size 0.3s ease-in-out"; // Smooth animation

    element.addEventListener("mouseenter", () => {
        element.style.backgroundSize = "100% 60%"; // Expand highlight
    });

    element.addEventListener("mouseleave", () => {
        element.style.backgroundSize = "100% 0%"; // Shrink highlight
    });
}
function highlight_TextFadeMiddle(element, color = 'yellow') {

	// Initial setup
    element.style.position = "relative";
    element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
    element.style.backgroundRepeat = "no-repeat";
    element.style.transition = "background-size 0.3s ease-in-out, background-position 0.3s ease-in-out";
    element.style.backgroundSize = "0% 60%";   // Start with no width
    element.style.backgroundPosition = "0 85%";  // Anchored on left

    element.addEventListener("mouseenter", () => {
        // Grow the highlight from left to right
        element.style.backgroundPosition = "0 85%";  // Anchor on left
        element.style.backgroundSize = "100% 60%";     // Expand to full width
    });

    element.addEventListener("mouseleave", () => {
        // First, shift the highlight so its right edge is anchored
        element.style.backgroundPosition = "100% 85%";
        // Then, shrink its width back to 0%
        // (A short delay can help ensure the position change is registered before the size transition starts.)
        setTimeout(() => {
            element.style.backgroundSize = "0% 60%";
        }, 10);
    });
}
function highlightPermanent_TextPop_WithEvents(element, color = 'yellow') {

    // Create event handler functions
    element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
    element.style.backgroundSize = "100% 60%"; // Highlight thickness
    element.style.backgroundPosition = "0 85%"; // Position below text
    element.style.backgroundRepeat = "no-repeat";

    const mouseLeaveHandler = () => {
        element.style.backgroundImage = "none"; // Remove highlight
    };

    const clickHandler = () => {
        element.style.backgroundImage = "none";  // Remove the highlight briefly
        setTimeout(() => {
            element.style.backgroundImage = `linear-gradient(to right, ${color} 50%, ${color} 50%)`;
            element.style.backgroundSize = "100% 60%";
            element.style.backgroundPosition = "0 85%";
            element.style.backgroundRepeat = "no-repeat";
        }, 150);  // 150ms delay
    };

    // Add event listeners
    element.addEventListener("click", clickHandler);

    // To remove the event listeners when necessary:
    element.removeEventListeners = () => {
        element.removeEventListener("click", clickHandler);
    };
}
function highlightPicked(element, bgcolor = 'black', color = 'white') {

	//arrow
	// element.style.color = color;
    // element.style.backgroundColor = bgcolor;
    // element.style.paddingLeft = "15px";
	// element.style.paddingRight = "10px";
	// element.style.clipPath = "polygon(100% 100%, 15px 100%, 0% 50%, 15px 0%, 100% 0%)";

	//two arrows
	element.style.color = color;
    element.style.backgroundColor = bgcolor;
    element.style.paddingLeft = "30px";
	element.style.paddingRight = "10px";
	element.style.clipPath = "polygon(100% 100%, 30px 100%, 20px 50%, 10px 100%, 0% 100%, 0% 0%, 10px 0%, 20px 50%, 30px 0%, 100% 0%)";

	//simple
	// element.style.color = color;
    // element.style.backgroundColor = bgcolor;
}

function unhighlight_Text(element) {

    element.style.backgroundImage = "";
    element.style.backgroundSize = "";
    element.style.backgroundPosition = "";
    element.style.backgroundRepeat = "";
}

