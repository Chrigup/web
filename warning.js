window.onload = function() {
    var myForm = document.getElementsByTagName('form')[0];

    myForm.onsubmit = function() {
        var name = document.getElementsByName('name')[0].value.trim();
        var tel = document.getElementsByName('tel')[0].value.trim();
        var email = document.getElementsByName('email')[0].value.trim();

        if (name === "" || name == null) {
            alert("姓名不能为空，请输入您的姓名！");
            return false;
        }

        var telReg = /^\d{11}$/;
        if (!telReg.test(tel)) {
            alert("手机号码格式不正确，请输入11位数字！");
            return false;
        }

        if (email.indexOf("@") === -1) {
            alert("邮箱格式不正确，必须包含 @ 符号！");
            return false;
        }

        alert("信息校验成功，正在提交简历...");
        return true;
    };
};