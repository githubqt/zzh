export function TMap(key) {
    key = key || 'HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV';
    return new Promise(function(resolve, reject) {
        window.init = function() {
            resolve(qq) //注意这里
        }
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = "https://map.qq.com/api/js?v=2.exp&callback=init&key=" + key;
        script.onerror = reject;
        document.head.appendChild(script);
    })
}

