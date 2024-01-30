const sendMessageButton = document.getElementById('sendMessageButton');
const messageContent = document.getElementById('messageContent');
const chatId = document.getElementById('chatId');
const messageContainer = document.getElementById('chatContent');

messageContainer.scrollTop = messageContainer.scrollHeight;

const socket = io('ws://localhost:3000');

const messageStack = new CallStack(2000);

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

socket.on('userInformation', (data) => {
    document.querySelector('.authorizationBanner').classList.add('fadeOut');
});

socket.on('sendMessage', (data) => {
    console.log('Received message from socket server', data);

    // Handle incoming message
    const incomingChatId = data.chatId;
    const message = data.message;

    const notificationAudio = new Audio('/assets/audio/notification.mp3');
    notificationAudio.play().then(r => console.log('Notification sound played.'));

    insertChat(incomingChatId, message, true);
    updateRecentText(incomingChatId, message);

    showNotification(data.creator, message);

    messageContainer.scrollTop = messageContainer.scrollHeight;
});

function showNotification(username, message) {
    if (!("Notification" in window)) {
        alert("This browser does not support desktop notification");
    } else {
        Notification.requestPermission().then(function (permission) {
            if (permission === "granted") {
                let notification = new Notification("SymfonyChat: " + username, {
                    icon: "/assets/images/favicon.png",
                    body: message,
                });

                setTimeout(function () {
                    notification.close();
                }, 5000);
            }
        });
    }
}

function getCurrentTime() {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');

    return hours + ':' + minutes;
}

function insertChat(incomingChatId, incomingMessage, receiving = true) {
    if (incomingChatId !== chatId.value) {
        return false;
    }

    const messageElement = document.createElement('div');
    messageElement.classList.add('w-100', 'd-flex', 'justify-content-start');

    if (!receiving) {
        messageElement.classList.remove('justify-content-start');
        messageElement.classList.add('justify-content-end');
    }

    messageElement.innerHTML = `
        <div class="recipientMessage d-inline-block text-break ${receiving === false ? 'ms-auto bg-black opacity-75' : 'me-auto bg-body-tertiary'} rounded p-2 my-2" style="min-width: 150px; max-width: 65%;">
            <div class="message ${receiving === false ? 'text-end' : 'text-start'}">
                <span>${incomingMessage}</span>
            </div>
            <div class="messageInformation text-end d-flex align-items-center justify-content-end mt-1" style="height: 14px;">
                <span class="text-muted" style="font-size: 12px;">${getCurrentTime()}</span>
            </div>
        </div>
    `;

    messageContainer.appendChild(messageElement);

    messageContainer.scrollTop = messageContainer.scrollHeight;

    return messageElement;
}

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

    const messageElement = insertChat(chatId.value, message, false);

    messageStack.add(() => {
        fetch('/api/chat/send/' + chatId.value, {
            method: 'POST',
            body: formData
        }).then(response => {
            if (response.status === 200) {
                return response.json();
            }
        }).then(data => {
            if (data.success) {
                messageElement.querySelector('.recipientMessage').classList.remove('opacity-75');

                socket.emit('sendMessage', {
                    chatId: chatId.value,
                    message: data.message,
                    creator: data.creator,
                });

                updateRecentText(chatId.value, data.message, false);
            } else {
                messageElement.classList.remove('bg-black');
                messageElement.classList.add('bg-danger');
            }
        });
    })
}

sendMessageButton.addEventListener('click', () => {
    sendMessage();
});

messageContent.addEventListener('keyup', (event) => {
    if (event.key === 'Enter') {
        sendMessage();
    }
});