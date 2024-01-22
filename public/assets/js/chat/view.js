const sendMessageButton = document.getElementById('sendMessageButton');
const messageContent = document.getElementById('messageContent');
const chatId = document.getElementById('chatId');
const messageContainer = document.getElementById('chatContent');

messageContainer.scrollTop = messageContainer.scrollHeight;

const socket = io('ws://localhost:3000');

socket.on('connect', () => {
    console.log('Connected to socket server');

    fetch('/api/auth/getInfo', {
        method: 'POST'
    }).then(response => {
        if (response.status === 200) {
            return response.json();
        }
    }).then(data => {
        if (data.success) {
            console.log(data);
            console.log('Sending user information to socket server');

            socket.emit('userInformation', {
                username: data.username,
                chatId: chatId.value ? chatId.value : null,
                availableChats: data.availableChats,
            });
        }

    })
});

socket.on('sendMessage', (data) => {
    console.log('Received message from socket server', data);

    // Handle incoming message
    const incomingChatId = data.chatId;
    const message = data.message;

    if (incomingChatId !== chatId.value) {
        return false;
    }

    const notificationAudio = new Audio('/assets/audio/notification.mp3');
    notificationAudio.play().then(r => console.log('Notification sound played.'));

    // Create message element
    const messageElement = document.createElement('div');
    messageElement.classList.add('recipientMessage');
    messageElement.classList.add('rounded');
    messageElement.classList.add('bg-black');
    messageElement.classList.add('p-2');
    messageElement.classList.add('w-50');
    messageElement.classList.add('my-2');

    const messageTextDiv = document.createElement('div');
    messageTextDiv.classList.add('message');

    const messageText = document.createElement('span');
    messageText.innerHTML = message;

    messageTextDiv.appendChild(messageText);
    messageElement.appendChild(messageTextDiv);
    messageContainer.appendChild(messageElement);
    // Create message element

    updateRecentText(incomingChatId, message);

    messageContainer.scrollTop = messageContainer.scrollHeight;
});

function updateRecentText(incomingChatId, message, updateUnreadMessages = true) {
    const recentText = document.querySelector('div[data-chatid="' + incomingChatId + '"] .recentText');
    recentText.innerHTML = message;

    if (!updateUnreadMessages || incomingChatId === chatId.value) { return; }

    let notificationBadge = document.querySelector('div[data-chatid="' + incomingChatId + '"] .unreadMessages');
    if (notificationBadge.innerHTML === '') {
        notificationBadge.innerHTML = '1';
    } else {
        notificationBadge.innerHTML = (parseInt(notificationBadge.innerHTML) + 1).toString();
    }
}

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

            socket.emit('sendMessage', {
                chatId: chatId.value,
                message: data.message,
                creator: data.creator,
            });

            updateRecentText(chatId.value, data.message, false);
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