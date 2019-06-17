window.fbAsyncInit = function() {
    FB.init({
    appId      : '332265930785866',
    cookie     : true,
    xfbml      : true,
    version    : 'v3.3' // use graph api version v3.3
    });

    // Check whether the user already logged in
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            //display user data
            FB.api('/me', {locale: 'en_GB', fields: 'id,first_name,last_name,email,link,gender,locale,picture'},
            function (response) {});
        }
    });
};

(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v3.3";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

// Save user data to the database
function save_user_data(response){
    $.post("/social_login", {
        auth_provider:'facebook',
        user_data: JSON.stringify(response),
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function(data){
        if (data.status == "proceed") {
            $(location).attr('href', data.redirect);
        }
    });
}

// Fetch the user profile data from facebook
function get_fb_user_data(){
    FB.api('/me', {locale: 'en_GB', fields: 'id,first_name,last_name,email,link,gender,locale,picture'},
    function (response) {
        // Save user data
        save_user_data(response);
    });
}

// Facebook login with JavaScript SDK
window.fb_login = function () {
    FB.login(function (response) {
        if (response.authResponse) {
            // login user with profile data
            get_fb_user_data();
        } else {
            document.getElementById('status').innerHTML = 'User cancelled login or did not fully authorize.';
        }
    }, {scope: 'email'});
}

// Logout from facebook and web app
window.logout_social = function() {
    FB.logout();
}
