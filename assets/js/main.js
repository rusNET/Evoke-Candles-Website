document.addEventListener("DOMContentLoaded", () => {
    // Adding fade-in animations to product elements
    const products = document.querySelectorAll(".product");
    products.forEach((product, index) => {
        product.classList.add("fade-in");
        product.style.animationDelay = `${index * 0.2}s`;
    });

    // Smooth scroll for links
    document.querySelectorAll("a[href^='#']").forEach(anchor => {
        anchor.addEventListener("click", function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute("href")).scrollIntoView({
                behavior: "smooth"
            });
        });
    });
});

