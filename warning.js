window.addEventListener("load", function () {
    var myForm = document.getElementsByTagName("form")[0];

    myForm.onsubmit = function () {
        var username = document.getElementsByName("username")[0].value.trim();
        var phone = document.getElementsByName("phone")[0].value.trim();
        var email = document.getElementsByName("email")[0].value.trim();

        if (username === "") {
            alert("姓名不能为空，请输入姓名。");
            return false;
        }

        var phoneReg = /^\d{11}$/;
        if (!phoneReg.test(phone)) {
            alert("手机号格式不正确，请输入 11 位数字。");
            return false;
        }

        if (email.indexOf("@") === -1) {
            alert("邮箱格式不正确，必须包含 @ 符号。");
            return false;
        }

        alert("信息校验成功，正在提交简历。");
        return true;
    };
});
