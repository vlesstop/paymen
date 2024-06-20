<div style="background: #eee">
    <table width="600" border="0" align="center" cellpadding="0" cellspacing="0">
        <tbody>
        <tr>
            <td>
                <div style="background:#fff">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <thead>
                        <tr>
                            <td valign="middle" style="padding-left:30px;background-color:#0b63f3;color:#fff;padding:20px 40px;font-size: 21px;font-weight:800">{{$name}}</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr style="padding:40px 40px 0 40px;display:table-cell">
                            <td style="font-size:24px;line-height:1.5;color:#000;margin-top:40px;font-weight:800">支付成功</td>
                        </tr>
                        <tr>
                            <td style="font-size:14px;color:#333;padding:24px 40px 0 40px">
                                尊敬的用户您好！
                                <br />
                                <br />
                                你已成功在本站订阅了 {{$subscribePlan}} - {{$subscribePeriod}} 套餐，感谢你的认可，请在 5 分钟内点击下方链接进行登入，<br />
                                <br />
                                <br />
                                <a href="{{$loginLink}}" style="padding: 8px 24px; background-color: #0b63f3;color: white;text-decoration: none">点击快速登录</a>
                            </td>
                        </tr>
                        <tr style="padding:40px;display:table-cell">
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                        <tr>
                            <td style="padding:20px 40px;font-size:12px;color:#999;line-height:20px;background:#f7f7f7"><a href="{{$url}}" style="font-size:14px;color:#929292">前往 {{$name}}</a></td>
                        </tr>
                        </tbody>
                    </table>
                </div></td>
        </tr>
        </tbody>
    </table>
</div>
