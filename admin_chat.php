<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Admin — Czat z użytkownikami</title>
<style>
body {
  font-family: 'Inter', sans-serif;
  background: #f5f7fb;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.chat-box {
  width: 420px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
  padding: 20px;
}
#messages {
  height: 250px;
  overflow-y: auto;
  border: 1px solid #ddd;
  border-radius: 6px;
  margin-bottom: 10px;
  padding: 8px;
  background: #fafafa;
}
.message { padding: 6px; margin: 3px 0; border-radius: 6px; }
.admin { background: #d1ecf1; text-align: right; }
.user { background: #e8f5e9; text-align: left; }
</style>
</head>
<body>
<div class="chat-box">
  <h2>💼 Czat z użytkownikami</h2>
  <div id="messages"></div>
  <form id="chatForm">
    <input id="msgInput" placeholder="Napisz wiadomość..." style="width:80%;padding:10px;">
    <button style="padding:10px;">Wyślij</button>
  </form>
</div>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
const socket = io("http://localhost:3000");
socket.emit("register", "admin", "Administrator");

const messages = document.getElementById("messages");
const form = document.getElementById("chatForm");
const input = document.getElementById("msgInput");

form.addEventListener("submit", e => {
  e.preventDefault();
  const text = input.value.trim();
  if (!text) return;
  const msg = { user: "Administrator", text };
  socket.emit("chat_message", msg);
  addMessage(msg);
  input.value = "";
});

socket.on("chat_message", msg => addMessage(msg));

function addMessage(msg) {
  const div = document.createElement("div");
  div.className = "message " + (msg.user === "Administrator" ? "admin" : "user");
  div.textContent = `${msg.user}: ${msg.text}`;
  messages.appendChild(div);
  messages.scrollTop = messages.scrollHeight;
}
</script>
</body>
</html>
