//自作関数
/** デバッグモードかどうか。本番公開時にはfalseにする */
let DEBUG_MODE = true;

/** デバッグモードでConsoleAPIが有効な場合にログを出力する */ 
function trace(s) {
    if (DEBUG_MODE && this.console && typeof console.log != "undefined") {
        console.log(s);
    }
}

export default {
    trace,
};
