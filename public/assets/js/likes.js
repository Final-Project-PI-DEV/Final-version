document.addEventListener('DOMContentLoaded', function () {
    const likeButtons = document.querySelectorAll('.btn-like');

    likeButtons.forEach(button => {
        button.addEventListener('click', async function () {
            const postId = this.getAttribute('data-post-id');
            const likeIcon = this.querySelector('.like-icon');
            const likeCount = this.parentElement.querySelector('.like-count');

            // Optimistically update the UI
            const currentCount = parseInt(likeCount.textContent);
            const liked = likeIcon.classList.contains('fas');  // Check if the post is already liked

            if (liked) {
                likeCount.textContent = currentCount - 1; // Decrement likes count
                likeIcon.classList.replace('fas', 'far'); // Change to unfilled heart
                likeIcon.style.color = 'black'; // Reset the color to black
            } else {
                likeCount.textContent = currentCount + 1; // Increment likes count
                likeIcon.classList.replace('far', 'fas'); // Change to filled heart
                likeIcon.style.color = 'red'; // Set color to red
            }

            try {
                const response = await fetch(`/post/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to update likes');
                }

                const data = await response.json();
                likeCount.textContent = data.likes; // Update with actual server data
            } catch (error) {
                console.error('Error:', error);
                // Revert UI changes in case of an error
                likeCount.textContent = currentCount;
                if (liked) {
                    likeIcon.classList.replace('fas', 'far');
                    likeIcon.style.color = 'black';
                } else {
                    likeIcon.classList.replace('far', 'fas');
                    likeIcon.style.color = 'red';
                }
            }
        });
    });
});
