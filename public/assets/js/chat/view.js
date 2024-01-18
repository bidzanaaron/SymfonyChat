const sendMessageButton = document.getElementById('sendMessageButton');
const messageContent = document.getElementById('messageContent');
const chatId = document.getElementById('chatId');

sendMessageButton.addEventListener('click', () => {
    const message = messageContent.value;
    if (message.length === 0) {
        return false;
    }

    const formData = new FormData();
    formData.append('message', message);

    fetch('/api/chat/send/' + chatId.value, {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.status === 200) {
            return response.json();
        }
    }).then(data => {
        if (data.success) {
            messageContent.value = '';
            // const messageContainer = document.getElementById('messageContainer');
            // const messageElement = document.createElement('div');
            // messageElement.classList.add('message');
            // messageElement.classList.add('message-sent');
            // messageElement.innerHTML = message;
            // messageContainer.appendChild(messageElement);
        }
    });
});