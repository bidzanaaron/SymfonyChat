const sendMessageButton = document.getElementById('sendMessageButton');
const messageContent = document.getElementById('messageContent');
const chatId = document.getElementById('chatId');
const messageContainer = document.getElementById('chatContent');

messageContainer.scrollTop = messageContainer.scrollHeight;

function sendMessage() {
    const message = messageContent.value;
    if (message.length === 0) {
        return false;
    }

    messageContent.value = '';

    const formData = new FormData();
    formData.append('message', message);

    // Create message element
    const messageElement = document.createElement('div');
    messageElement.classList.add('recipientMessage');
    messageElement.classList.add('ms-auto');
    messageElement.classList.add('rounded');
    messageElement.classList.add('bg-primary');
    messageElement.classList.add('p-2');
    messageElement.classList.add('w-50');
    messageElement.classList.add('my-2');
    messageElement.classList.add('opacity-75')

    const messageTextDiv = document.createElement('div');
    messageTextDiv.classList.add('message');
    messageTextDiv.classList.add('text-end');

    const messageText = document.createElement('span');
    messageText.innerHTML = message;

    messageTextDiv.appendChild(messageText);
    messageElement.appendChild(messageTextDiv);
    messageContainer.appendChild(messageElement);

    messageContainer.scrollTop = messageContainer.scrollHeight;
    // Create message element

    fetch('/api/chat/send/' + chatId.value, {
        method: 'POST',
        body: formData
    }).then(response => {
        if (response.status === 200) {
            return response.json();
        }
    }).then(data => {
        if (data.success) {
            messageElement.classList.remove('opacity-75');
        } else {
            messageElement.classList.remove('bg-primary');
            messageElement.classList.add('bg-danger');
        }
    });
}

sendMessageButton.addEventListener('click', () => {
    sendMessage();
});

messageContent.addEventListener('keyup', (event) => {
    if (event.key === 'Enter') {
        sendMessage();
    }
});