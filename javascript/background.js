var successURL = 'https://www.facebook.com/connect/login_success.html';
function onFacebookLogin() {
    if (!localStorage.accessToken) {

        chrome.tabs.getAllInWindow(null, function (tabs) {
            for (var i = 0; i < tabs.length; i++) {
                var ctab = tabs[i];
                if (ctab.url.indexOf(successURL) == 0) {
                    var params = ctab.url.split('#')[1];
                    access = params.split('&')[0]
                    console.log(access);
                    localStorage.accessToken = access;
                    chrome.tabs.remove(ctab.id);

                    return;
                }
            }
        });
    }
}


var callbacks = {
    reset_badge: function (request, sender) {
        setTimeout(function () {
            chrome.browserAction.setBadgeText({text: ""});
        }, 5000);
    }
};

chrome.tabs.onUpdated.addListener(onFacebookLogin);
chrome.runtime.onMessage.addListener(function (request, sender, sendResponse) {
    if (request.type == "callback") {
        callbacks[request.fn].call(sender, request, sender);
    }
});