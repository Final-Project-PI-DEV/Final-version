document.addEventListener('DOMContentLoaded', () => {
    console.log('Document loaded');

    document.body.addEventListener('click', async (e) => {
        if (e.target.classList.contains('submit-comment')) {
            console.log("Submit button clicked");

            const button = e.target;
            const postId = button.dataset.postId;
            const input = document.querySelector(`#comment-section-${postId} .comment-input`);
            const content = input.value.trim();

            console.log("Post ID:", postId);
            console.log("Content:", content);

            if (!content) {
                alert("Please write a comment before submitting.");
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(`/post/${postId}/comment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ content })
                });

                console.log("Response received:", response);

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'Server error');
                }

                console.log("Comment saved successfully:", result);

                // Add new comment
                const commentsList = document.getElementById(`comments-${postId}`);
                commentsList.insertAdjacentHTML('afterbegin', 
                    `<div class="comment-item small mb-2">
                        ${result.content}
                        <small class="text-muted">${result.created_at}</small>
                    </div>`
                );

                // Update counter
                const counter = button.closest('.card-body').querySelector('.comment-count');
                counter.textContent = parseInt(counter.textContent) + 1;

                // Clear input
                input.value = '';

            } catch (error) {
                console.error('Error:', error);
                alert(error.message || "Failed to save comment");
            } finally {
                button.disabled = false;
            }
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".comment-form").forEach(form => {
        form.addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent navigation

            let formData = new FormData(this);
            let url = this.action;
            let errorContainer = this.querySelector(".error-messages");
            errorContainer.innerHTML = ""; // Clear previous errors

            fetch(url, {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Comment added successfully!"); 
                    location.reload(); // Reload page to show new comment
                } else if (data.errors) {
                    data.errors.forEach(error => {
                        let errorMessage = document.createElement("div");
                        errorMessage.className = "alert alert-danger mt-2";
                        errorMessage.innerText = error;
                        errorContainer.appendChild(errorMessage);
                    });
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});
// Edit button: Toggle comment visibility and show edit form
function editComment(commentId, currentContent) {
    document.getElementById('comment-content-' + commentId).style.display = 'none';
    document.getElementById('edit-form-' + commentId).classList.remove('d-none');
    document.getElementById('edit-input-' + commentId).value = currentContent;
}

// Cancel edit: Hide edit form and show the original comment content
function cancelEdit(commentId) {
    document.getElementById('comment-content-' + commentId).style.display = 'block';
    document.getElementById('edit-form-' + commentId).classList.add('d-none');
}
