const socket = io('ws://localhost:3000');

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

            let chatId = null;
            if (data.chatId !== null) {
                chatId = data.chatId;
            }

            socket.emit('userInformation', {
                username: data.username,
                chatId: chatId,
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

    const notificationAudio = new Audio('/assets/audio/notification.mp3');
    notificationAudio.play().then(r => console.log('Notification sound played.'));

    document.querySelector('div[data-chatid="' + data.chatId + '"] .recentText').innerHTML = data.message;

    let notificationBadge = document.querySelector('div[data-chatid="' + data.chatId + '"] .unreadMessages');
    if (notificationBadge.innerHTML === '') {
        notificationBadge.innerHTML = '1';
    } else {
        notificationBadge.innerHTML = (parseInt(notificationBadge.innerHTML) + 1).toString();
    }

    showNotification(data.creator, data.message);
});