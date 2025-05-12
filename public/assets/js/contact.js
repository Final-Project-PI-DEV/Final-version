document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".faq-item h3").forEach(item => {
        item.addEventListener("click", function() {
            let paragraph = this.nextElementSibling;
            paragraph.classList.toggle("show");
        });
    });
    
});
