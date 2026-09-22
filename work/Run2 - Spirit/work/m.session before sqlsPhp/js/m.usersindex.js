// Redirect when clicked
function searchClick() {

    const input = document.getElementById("searchInput").value.trim();
    if (!input) return; // empty

    // check for non-alphanumeric characters
    if (!/^[a-z0-9]+$/i.test(input)) return;

    const url = window.location.pathname + "?search=" + encodeURIComponent(input);
    window.location.href = url;
}

// Search when typing
function search() {

	const input = document.getElementById("searchInput").value.toLowerCase();
	const users = document.querySelectorAll(".user-item");

	users.forEach(user => {
		const username = user.textContent.toLowerCase();

		if (username.includes(input)) {
			user.style.display = "";
		} else {
			user.style.display = "none";
		}
	});
	
	// has to do with letter headers
	// when feature is active and when we have more than like 150 users
	// otherwise it makes no sense to use headers functionally and estetically
	const letters = document.querySelectorAll(".user-letter");
	if(input !== "") letters.forEach(letter => {letter.style.display = "none";});
	else letters.forEach(letter => {letter.style.display = "block";});
}

// Optional: search while typing
//document.getElementById("searchInput").addEventListener("keyup", search);