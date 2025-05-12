// Flash message fade-out
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
        let flashMessages = document.querySelectorAll(".flash-message");
        flashMessages.forEach(function (message) {
            message.style.transition = "opacity 1s";
            message.style.opacity = "0";
            setTimeout(() => (message.style.visibility = "hidden"), 1000);
        });
    }, 1000);
});

// Chatbot functionality
document.addEventListener("DOMContentLoaded", function () {
    const chatbotButton = document.getElementById("chatbotButton");
    const chatbotInterface = document.getElementById("chatbotInterface");
    const closeChatbot = document.getElementById("closeChatbot");
    const chatInput = document.getElementById("chatInput");
    const sendMessageButton = document.getElementById("sendMessage");
    const chatbotBody = document.getElementById("chatbotBody");

    // Toggle chatbot interface
    chatbotButton.addEventListener("click", () => {
        chatbotInterface.style.display = "flex";
    });

    // Close chatbot interface
    closeChatbot.addEventListener("click", () => {
        chatbotInterface.style.display = "none";
    });

    // Send message
    sendMessageButton.addEventListener("click", () => {
        const message = chatInput.value.trim();
        if (message) {
            // Add user message
            const userMessage = document.createElement("div");
            userMessage.className = "chat-message user-message";
            userMessage.innerHTML = `<strong>You:</strong> ${message}`;
            chatbotBody.appendChild(userMessage);

            // Clear input
            chatInput.value = "";

            // Scroll to bottom
            chatbotBody.scrollTop = chatbotBody.scrollHeight;

            // Simulate bot response
            setTimeout(() => {
                const botMessage = document.createElement("div");
                botMessage.className = "chat-message bot-message";
                botMessage.innerHTML = `<strong>Fern:</strong> Je ne comprends pas encore cette question. Pouvez-vous reformuler ?`;
                chatbotBody.appendChild(botMessage);

                // Scroll to bottom
                chatbotBody.scrollTop = chatbotBody.scrollHeight;
            }, 1000);
        }
    });

    // Send message on Enter key
    chatInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            sendMessageButton.click();
        }
    });
});