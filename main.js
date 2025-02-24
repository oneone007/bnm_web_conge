function loadPage(page) {
    fetch(`/bnm_web/router.php?page=${page}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById("content").innerHTML = data;
            window.history.pushState({}, "", `/bnm_web/${page}`); // Change URL
        })
        .catch(error => console.error('Error loading page:', error));
}

// Attach event listeners
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".nav-link").forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            loadPage(this.getAttribute("data-page"));
        });
    });
});
