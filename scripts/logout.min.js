document.addEventListener("DOMContentLoaded", function() {
    var links = document.querySelectorAll('a[href*="logout"]');
    links.forEach(function(link) {
        if (link.href.indexOf("openid/logout") === -1) {
            var baseUrl = window.CFG_GLPI ? window.CFG_GLPI.url_base : "";
            link.href = baseUrl + "/plugins/openid/logout";
        }
    });
});
