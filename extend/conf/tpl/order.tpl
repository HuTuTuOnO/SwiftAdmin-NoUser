<div style="background-color:#ECECEC; padding: 35px;">
    <table cellpadding="0" align="center"
           style="width: 80%; margin: 0px auto; text-align: left; position: relative; border-radius: 5px;font-size: 14px; font-family:微软雅黑, 黑体; line-height: 1.5; box-shadow: rgb(153, 153, 153) 0px 0px 5px; border-collapse: collapse; background: #fff initial initial initial initial;">
        <tbody>
        <tr>
            <th style="height: 25px; line-height: 25px; padding: 15px 35px; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #C46200; background-color: #e01b3c; border-radius: 5px 5px 0px 0px;">
                <span style="color: rgb(255, 255, 255);  font-family: 微软雅黑; font-size: large; ">{site_name}</span>
            </th>
        </tr>
        <tr>
            <td>
                <div style="padding:25px 35px 40px; background-color:#fff;">
                    <h2 style="margin: 5px 0px; ">
                        <span style="line-height: 20px;  color: #333333; ">
                            <span style="line-height: 22px;  font-size: large; ">尊敬的开发者 {nickname}：</span>
                        </span>
                    </h2>
                    <p style="font-size:14px;"> 您有新的插件订单，用户已经完成付款啦。</p>
                    <p>插件名称：{title}</p>
                    <p style="color: red;">订单号：{order_id}</p>
                    <p style="color: red;">付款金额：<span style="font-weight: bold">{price}</span></p>
                    <p>付款时间：{update_time}</p>
                    <p>如果您有什么疑问可以联系管理员，Email: {email}。</p>
                    <p align="right" style="font-size:14px;">{site_name} 官方团队</p>
                    <p align="right" style="font-size:14px;">{time}</p></div>
            </td>
        </tr>
        </tbody>
    </table>
</div>