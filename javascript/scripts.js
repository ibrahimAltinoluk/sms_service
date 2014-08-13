(function ($) {

var api_url = "http://www.ibrahimaltinoluk.com/web/sms_service/index.php";

var isnumeric = function (str) {
    var pattern = /^\d+$/;
    return pattern.test(str);
};

var add_contact = function (i, item, call_back) {
    var o = $("<div>", {text: item.title + " <" + item.phone + ">", "data-phone": item.phone});
    var c = $("<i>", {class: "icon-remove"}).hide().click(function () {
        $(this).parent().remove();
    });

    o.prepend(c).click(function () {
        $(this).appendTo("#to_list");
    }).hover(function () {
            $(this).find(".icon-remove").show();
        }, function () {
            $(this).find(".icon-remove").hide();
        });

    $("#search_result").append(o);
    if (call_back) {
        call_back.call(this, o, c);
    }

};

var ajax = function (options) {

    var o = {
        url: api_url,
        dataType: "json",
        type: "post",
    };

    o = $.extend(o, options);
    $.ajax(o);
};

$("#phone").keyup(function (key) {


    if (key.keyCode == 13) {
        if (isnumeric($(this).val())) {
            add_contact(0, {title: "NoName", phone: $(this).val()}, function (o, c) {
                o.click();
            });
        }
        return;
    }

    ajax({
        data: {method: "get_contacts", token: localStorage.accessToken, keyword: $("#phone").val()},
        success: function (data) {
            $("#search_result").empty();
            $.each(data.results, add_contact);
        }
    });

});

$("#confirm").click(function () {


    var to = [], number = [];

    $("#to_list").find("div").each(function () {
        to.push($(this).parent().text());
        number.push($(this).attr("data-phone"));
    });


    var data = {
        token: localStorage.accessToken,
        device: $("#devices").val(),
        to: to.join(","),
        number: number.join(","),
        message: $("#message").val(),
        method: "send_sms"
    };


    ajax({
        data: data,
        success: function (data) {

            chrome.browserAction.setBadgeText({text: data.extention_data.text});
            chrome.browserAction.setBadgeBackgroundColor({color: data.extention_data.color});
            chrome.runtime.sendMessage({type: "callback", fn: "reset_badge"});
        }
    });
});


var getDevices = function (response) {

    $.each(response.devices, function (j, i) {
        $("#devices").append($("<option>", {value: i.deviceId, text: i.title}));
    });

    $(".login_required").show();
};

var register = function (user) {

    var data = {
        fbid: user.id,
        name: user.name,
        email: user.email,
        token: user.token,
        from: "chrome",
        method: "register_member"
    }

    ajax({
        data: data,
        success: getDevices
    });
};


window.getAccessToken = function () {
    var graphUrl = "https://graph.facebook.com/me?" + localStorage.accessToken + "&callback=displayUser";
    var script = document.createElement("script");
    script.src = graphUrl;
    document.body.appendChild(script);
};
window.displayUser = function (user) {
    if (user.error) {
        delete localStorage.accessToken;
        return;
    }

    user.token = localStorage.accessToken;
    localStorage.setItem("user", JSON.stringify(user));

    $("#logout").show().click(function () {
        delete localStorage.accessToken;
        location.reload();
    });

    $("#fblogin").hide().before([user.first_name , user.last_name, " "].join(" "));

    register(user);
    getDevices(user);

};


getAccessToken();

})($);

