var loader = document.getElementById("preloader");

window.addEventListener("load", function () {
    loader.style.display = "none";
    $("#spinner").hide();
    $("#alert-danger").attr("style", "display:none !important");
});

$("#read").click(function (evt) {
    var _token = $("#_token").val();
    $.ajax({
        //create an ajax request to get session data
        type: "POST",
        url: "read",
        data: { _token: _token }, //expect json File to be returned
        success: function (response) {
            $("#done").show();
            $("#read").hide();
        },
    });
});

const copyButtons = document.querySelectorAll(".copy-account-number");

copyButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
        const accountNumberElement = event.target
            .closest("li")
            .querySelector(".acctno");
        const accountNumber = accountNumberElement.textContent.trim();
        navigator.clipboard.writeText(accountNumber);
        event.target.innerHTML = "Copied";
    });
});

document.querySelectorAll(".copy-account-number2").forEach((button) => {
    button.addEventListener("click", async (event) => {
        // Find the closest container div
        const container = event.target.closest(".d-flex");

        // Get the account number text
        const accountNumberElement = container.querySelector(".acctno2");
        if (accountNumberElement) {
            const accountNumber = accountNumberElement.textContent.trim();

            try {
                // Attempt to use Clipboard API
                await navigator.clipboard.writeText(accountNumber);

                // Update button text to "Copied"
                button.textContent = "Copied";

                // Reset button text after 2 seconds
                setTimeout(() => {
                    button.textContent = "Copy";
                }, 2000);
            } catch (err) {
                console.error("Clipboard copy failed:", err);

                // Fallback: Create a temporary input element
                const tempInput = document.createElement("input");
                tempInput.value = accountNumber;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);

                // Update button text
                button.textContent = "Copied";
                setTimeout(() => {
                    button.textContent = "Copy";
                }, 2000);
            }
        }
    });
});


$("#upgrade").on("click", function (event) {
    let data = new FormData(this.form);
    $.ajax({
        type: "post",
        url: "upgrade",
        data,
        dataType: "json",
        processData: false,
        contentType: false,
        cache: false,
        success: function (response) {
            $("#successMsg").show();
            $("#smessage").html(response.msg);
            setTimeout(function () {
                location.reload();
            }, 5000);
        },
        error: function (data) {
            $.each(data.responseJSON.errors, function (key, value) {
                $("#errorMsg").show();
                $("#message").html(value);
            });
            setTimeout(function () {
                $("#errorMsg").hide();
            }, 5000);
        },
    });
});

function identifyNetwork(prefix) {
    var mtnPrefixes = [
        "0803",
        "0806",
        "0703",
        "0706",
        "0813",
        "0814",
        "0816",
        "0903",
        "0810",
        "0906",
        "0913",
        "0916",
        "0702",
        "0704",
    ];
    var airtelPrefixes = [
        "0802",
        "0902",
        "0701",
        "0808",
        "0708",
        "0812",
        "0901",
        "0904",
        "0907",
        "0912",
        "0911",
    ];
    var gloPrefixes = ["0805", "0807", "0815", "0811", "0705", "0905", "0915"];
    var etisalatPrefixes = ["0809", "0817", "0818", "0909", "0908"];

    if (mtnPrefixes.includes(prefix)) {
        return "MTN";
    } else if (airtelPrefixes.includes(prefix)) {
        return "Airtel";
    } else if (gloPrefixes.includes(prefix)) {
        return "GLO";
    } else if (etisalatPrefixes.includes(prefix)) {
        return "9mobile";
    } else {
        return "Unknown Network";
    }
}

function validateNumber() {
    var phoneNumberInput = document.querySelector(".phone");
    //var bypassCheckbox = document.getElementById("bypassValidation");
    var networkResult = document.getElementById("networkResult");
    var phoneNumber = phoneNumberInput.value.replace(/[^0-9]/g, "");
    // && !bypassCheckbox.checked
    if (phoneNumber.length >= 4) {
        var prefix = phoneNumber.substring(0, 4);
        var network = identifyNetwork(prefix);
        networkResult.textContent = "Network Identified: " + network;
        networkResult.classList.add(
            "p-3",
            "bg-info",
            "bg-opacity-10",
            "border",
            "border-info",
            "rounded",
            "rounded-top-0",
            "border-top-0",
            "mt-0"
        );
    } else {
        networkResult.textContent = "";
        networkResult.classList.remove(
            "p-3",
            "bg-info",
            "bg-opacity-10",
            "border",
            "border-info",
            "rounded",
            "rounded-top-0",
            "border-top-0",
            "mt-0"
        );
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const logoutBtn = document.getElementById("logoutForm");

    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            try {
                if (typeof localStorage !== "undefined") {
                    localStorage.removeItem("anouncementModalShown");
                }
            } catch (error) {
                console.warn("Failed to remove item from localStorage:", error);
            }
        });
    }
});

