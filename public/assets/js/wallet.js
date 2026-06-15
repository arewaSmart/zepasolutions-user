$(document).ready(function () {
    $("#Wallet_ID").on("blur", function () {
        let walletID = $("#Wallet_ID").val();
        let reciever = document.getElementById("reciever");

        if (walletID != "") {
            $.ajax({
                //create an ajax request to get session data
                type: "get",
                url: "getReciever",
                dataType: "json", //expect json File to be returned
                data: { walletID: walletID },
                success: function (response) {
                    if (response == 0) {
                        error_message(null);
                    } else if (response == "kyc") {
                        error_message("Reciever Pending KYC");
                    } else {
                        success_message(response);
                    }
                },
                error: function (data) {
                    error_message(null);
                },
            });
        } else {
            error_message("Please Enter a valid wallet ID");
        }
    });

    function error_message(msg) {
        if (msg) reciever.textContent = msg;
        else reciever.textContent = "Cannot Verify Reciever";
        reciever.className = "alert alert-danger py-2 px-3 mt-2 text-center fs-12 border-0 d-block";
    }
    function success_message(response) {
        reciever.textContent = "Receiver: " + response;
        reciever.className = "alert alert-success py-2 px-3 mt-2 text-center fs-12 border-0 d-block";
    }
});
