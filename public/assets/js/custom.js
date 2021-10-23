var regForm = document.querySelector('#reg-form')
var regFormDiv = document.querySelector('.reg_form')
var otpFormDiv = document.querySelector('.otp_form')
var mobile = document.querySelector('#phone-code')
var customername = document.querySelector('#regName')
var email = document.querySelector('#regEmail')
var pwd = document.querySelector('#regPass')
var conformPwd = document.querySelector('#regCPass')
var countryCode = document.querySelector('#countryCode')
var otp_msg = document.querySelector('.otp_msg')
var otpNo = Math.floor(1000 + Math.random() * 9000);

var show_error = function (message) {
    $("#flash-messages").show()
    message = message || 'Error!!!!';
    $("#flash-messages").html('<div class="alert alert-danger alert-dismissible fade show" role="alert">'+message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    return false;
  };
  function validatephonenumber(inputtxt)
{
  var phoneno = /^\d{10}$/;
  if((inputtxt.match(phoneno))){
      return true;
        }
      else
        {
        return false;
        }
}
  function ValidateEmail(mail) 
{
 if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))
  {
    return (true)
  }
    return (false)
}
let timerOn = true;
function timer(remaining) {
    var m = Math.floor(remaining / 60);
    var s = remaining % 60;
    
    m = m < 10 ? '0' + m : m;
    s = s < 10 ? '0' + s : s;
    document.getElementById('timer').innerHTML = m + ':' + s;
    remaining -= 1;
    
    if(remaining >= 0 && timerOn) {
      setTimeout(function() {
          timer(remaining);
      }, 1000);
      return;
    }
  
    if(!timerOn) {
      // Do validate stuff here
      return;
    }
    
    // Do timeout stuff here
    document.getElementById('timer').innerHTML = "<a onclick='resendOTP()' class='resendOtp'>Resend OTP</a>"
  }
function submitRegister(){
    if(email.value && customername.value && pwd.value && mobile.value){
        if(!ValidateEmail(email.value)){
            show_error('Please Enter Valid mail address')
        }
        else if(!validatephonenumber(mobile.value)){
            show_error('Phone number must be 10 digit')
        }
        else if(pwd.value.length<6){
            show_error('Password must be at least 6 characters long.')
        }
        else if(pwd.value != conformPwd.value ){
            show_error('Password Missmatch')
        }
        else if(!$("#agreeCheckbox").is(":checked")){
            show_error('Please check agree to the conditions')
        }
        else{
            // regFormDiv.style.display = "none"
            // otpFormDiv.style.display = "block"
            $("#flash-messages").hide()
            axios.get('https://cors.bridged.cc/https://api.msg91.com/api/v5/otp?template_id=6103d8e33bffe75563413c56&authkey=364979A4oicv2Xxrs61022ba5P1&mobile='+countryCode.value+mobile.value+'&sender=YellCt&otp='+otpNo+'&otp_expiry=2&otp_length=4',{
                "headers": {
                    "content-type": "application/json"
                  }
            })
            .then(function (response) {
                // handle success
                console.log(response);
                if(response.data.type == "success"){
                    timer(120);
                    document.getElementById('createAccount').style.display = "none"
                    otp_msg.innerHTML = `Your One Time Password (OTP) has been sent to the number ${mobile.value}.`
                    regFormDiv.style.display = "none"
                    otpFormDiv.style.display = "block"
                }
              })
              .catch(function (error) {
                // handle error
                console.log(error);
                show_error('Error Sending OTP')
              })
        }
    }else{
        show_error('Please fill all the required fields')
    }
    
}

function submitOTP(){ 
    var otpValue = document.querySelector('#otpValue').value
    if(otpValue){
        console.log('https://cors.bridged.cc/https://api.msg91.com/api/v5/otp/verify?authkey=364979A4oicv2Xxrs61022ba5P1&mobile='+countryCode.value+mobile.value+'&otp='+otpValue)
        axios.get('https://cors.bridged.cc/https://api.msg91.com/api/v5/otp/verify?authkey=364979A4oicv2Xxrs61022ba5P1&mobile='+countryCode.value+mobile.value+'&otp='+otpValue,{
                "headers": {
                    "content-type": "application/json"
                  }
            })
            .then(function (response) {
                // handle success
                console.log(response);
                if(response.data.type == "success"){
                    regForm.submit();
                }else{
                  show_error(response.data.message)
                }
              })
              .catch(function (error) {
                // handle error
                console.log(error);
                show_error('Error Verifying OTP')
              })
    }
}

function resendOTP(){
    otpNo = Math.floor(1000 + Math.random() * 9000);
    axios.get('https://cors.bridged.cc/https://api.msg91.com/api/v5/otp?template_id=6103d8e33bffe75563413c56&authkey=364979A4oicv2Xxrs61022ba5P1&mobile='+countryCode.value+mobile.value+'&sender=YellCt&otp='+otpNo+'&otp_expiry=2&otp_length=4',{
                "headers": {
                    "content-type": "application/json"
                  }
            })
            .then(function (response) {
                // handle success
                console.log(response);
                if(response.data.type == "success"){
                    timer(120);
                    regFormDiv.style.display = "none"
                    otpFormDiv.style.display = "block"
                }
              })
              .catch(function (error) {
                // handle error
                console.log(error);
                show_error('Error Resending OTP')
              })

}