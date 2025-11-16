//mobile-detector utility
const mobileDetector = {
	
	IsAndroid: function() {

		return navigator.userAgent.match(/Android/i);
    },
	IsBlackBerry: function() {

		return navigator.userAgent.match(/BlackBerry/i);
    },
    IsIOS: function() {

		return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    IsOpera: function() {

		return navigator.userAgent.match(/Opera Mini/i);
    },
    IsWindows: function() {

		return navigator.userAgent.match(/IEMobile/i) ||
			navigator.userAgent.match(/WPDesktop/i);
    },
	IsMobile: function() {

		var isAndroid = mobileDetector.IsAndroid();
		if(isAndroid) return true;
		
		var isIos = mobileDetector.IsIOS();
		if(isIos) return true;
		
		var isOperaMobile = mobileDetector.IsOpera();
		if(isOperaMobile) return true;
		
		var isWindowsMobile = mobileDetector.IsWindows();
		if(isWindowsMobile) return true;
		
		return false;
    },

	getMedia: function() {

		if(this.IsMobile())
		{
			//alert("mobile");
			return "mobile";
		}
		else if(window.innerWidth < 850)
		{
			//alert("small");
			return "small";
		}
		else
		{
			//alert("pc");
			return "pc";
		}
	}
};
