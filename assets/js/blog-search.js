function performSearch() {
    const input = document.getElementById("searchInput").value.trim().toLowerCase();
    const resultContainer = document.getElementById("searchResults");
    const resultList = document.getElementById("resultsList");

    resultList.innerHTML = "";
    resultContainer.style.display = "none";

    if (input === "") return;

    // Grab data from existing HTML blog items
    const allBlogItems = document.querySelectorAll(".blog-slide-item, .blog_details_item, .recent_post_item, .blog-details_wrap");

    const results = [];

    allBlogItems.forEach(item => {
        const titleElement = item.querySelector(".xb-item--title a") || item.querySelector("h3 a");
        const authorElement = item.querySelector("span img + span"); // Adjust if author exists
        const title = titleElement?.textContent?.trim() || "";
        const author = authorElement?.textContent?.trim() || "Unknown";
        const link = titleElement?.getAttribute("href") || "#";

        if (title.toLowerCase().includes(input) || author.toLowerCase().includes(input)) {
            results.push({ title, author, link });
        }
    });

    if (results.length === 0) {
        resultList.innerHTML = "<li>No matching blog posts found.</li>";
    } else {
        results.forEach(post => {
            const li = document.createElement("li");
            li.classList.add("recent_post_item");
            li.innerHTML = `
                <h3 class="post-title border-effect-2">
                    <a href="${post.link}">${post.title}</a>
                </h3>
                <span><img src="assets/img/icon/profile-circle.svg" alt="">By ${post.author}</span>
            `;
            resultList.appendChild(li);
        });
    }

    resultContainer.style.display = "block";
}