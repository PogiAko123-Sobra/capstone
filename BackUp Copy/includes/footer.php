

<!-- Chatbot -->
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<link href="/css/1bot.css" rel="stylesheet">

<div class="chatbot-icon" id="chatbot-icon">
    <img src="Images/11bot.png" width="35">
</div>

<div class="wrapper" id="chatbot">
    <div class="title">
        VR Mental Wellness
        <div class="header-buttons">
            <span id="reset-chat" class="reset-btn"><i class="fas fa-sync-alt"><img src="Images/10reset.png" style="height: 14px; width: 14px;"  onmouseover="this.style.backgroundColor='white'" onmouseout="this.style.backgroundColor='transparent'"></i></span>
            <span id="close-chatbot" class="close-btn">&times;</span>
        </div>
    </div>
    <div class="form" id="chatbox-content">
        <div class="bot-inbox inbox">
            <div class="icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="msg-header">
                <p>Hi, how can I help you?</p>
            </div>
        </div>
    </div>
    <div class="typing-field">
        <div class="input-data">
            <input id="data" type="text" placeholder="Type something here.." required>
        </div>
        <button id="send-btn">Send</button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#chatbot-icon").click(function(){
            $("#chatbot").toggle();
        });

        $("#close-chatbot").click(function(){
            $("#chatbot").hide();
        });

        $("#reset-chat").click(function(){
            $("#chatbox-content").html('<div class="bot-inbox inbox"><div class="icon"><i class="fas fa-user"></i></div><div class="msg-header"><p>Hi, how can I help you?</p></div></div>');
        });

        function sendMessage(){
            let value = $("#data").val().trim();
            if (value === "") return;
            
            // Append user message
            let msg = '<div class="user-inbox inbox"><div class="msg-header"><p>'+ value +'</p></div></div>';
            $(".form").append(msg);
            
            // Clear input field
            $("#data").val('');

            // Append loading animation
            let loading = `
                <div class="bot-inbox inbox loading">
                    <div class="icon"><i class="fas fa-user"></i></div>
                    <div class="msg-header">
                        <div class="typing">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>`;
            $(".form").append(loading);
            $(".form").scrollTop($(".form")[0].scrollHeight);

            // Delay before sending the request to show the loading animation
            setTimeout(function() {
                $.ajax({
                    url: 'message.php',
                    type: 'POST',
                    data: { text: value },
                    success: function(result){
                        // Remove the loading animation
                        $(".loading").remove();
                        
                        // Append bot response
                        let reply = '<div class="bot-inbox inbox"><div class="icon"><i class="fas fa-user"></i></div><div class="msg-header"><p>'+ result +'</p></div></div>';
                        $(".form").append(reply);
                        $(".form").scrollTop($(".form")[0].scrollHeight);
                    }
                });
            }, 1500); // 1.3s delay for a smoother experience
        }

        $("#send-btn").on("click", sendMessage);
        $("#data").keypress(function(event){
            if (event.which == 13) {
                sendMessage();
            }
        });
    });
</script>

</body>
<footer class="footer mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-12 mb-3 text-center">
                <p class="mb-0 text-white">Developed by <span class="text-warning">Jelmar & Sophia</span></p>
            </div>
            <div class="col-md-6 col-12 text-center">
                <p class="mb-0 text-white">&copy; 2025 VR Emotional Well-being | <a href="#">Privacy Policy</a></p>
            </div>
        </div>
    </div>
</footer>
</html>
