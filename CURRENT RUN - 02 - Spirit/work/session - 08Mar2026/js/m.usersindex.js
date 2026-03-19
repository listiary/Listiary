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
}

// Optional: search while typing
//document.getElementById("searchInput").addEventListener("keyup", search);