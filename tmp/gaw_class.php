<?php
$RSA="-----BEGIN RSA PUBLIC KEY-----\nMIGJAoGBAKv4OKlpY2oq9QZPMzAjbQfiqDqTnisSvdLP+mTswZJdbtk1J+4+qAyS\nJuZjSQljzcUu0ANg+QG0VsvoU72zu5pErZKWubfe9HB/tq69bhP60qgP6/W2VebW\nlqUNGtsMedxuVaFBL3SoqU7e5RELIsuArCJJIgz86BQDX0x63VpXAgMBAAE=\n-----END RSA PUBLIC KEY-----";
$HOST1='54.183.10.158';			// "/spx_*"
$HOST2='ing0042.sphinxjoy.net';		// "/ING*"

$URLS=array(
	'spx_init'=>array(
		'GET'=>'/spx_root/index.php/api_root/spx_init',
		'POST'=>'pd={"app_key":"ING004","spx_did":1452143,"publish":"google","device":{"gp_adid":"0635f169-675c-46a2-bd7d-5746f87a110f","android_id":"-1","ios_idfa":"-1","mac_address":"-1","platform":"android"},"info":{"app_version":"1.8.1","os_version":"4.4.4","content_version":"0191","platform":"android","device_type":"Lenovo TAB 2 A10-70F 4.4.4"}}'
	),
	'spx_update'=>array(
		'GET'=>'/spx_update/api_update/checknew/ING004/google/1.8.1/0191/0/-1/default/ru/0191',
		'POST'=>'',
		'RES'=>'',
		'Set-Cookie'=>'Set-Cookie: updatesysci_session=Id4N9v%2FWyvDb4H4HLzAPC9nL1y%2FUCxOL6VEg6VlVwVwHaM35MYAryNbErk3xcVAVS%2F2YmgJZlaPLnezR2kYQMA9vmzHkcJAfprfgi7zK0G9v9PWIuQOylcI5U9OW8UemhZwaFtX10dXJ%2Ft1bBWIpEkdt3L06FgIrh9VAhKyBKlag7qSA1W3C3GbvPOvLWQoHFSve%2FArCgaIwTYCywVp%2FiG6tdM3lFZxU3zR0VL0lhiWpZW%2BsVp7BWH3url6nNE%2BbbttwidGN9EF3ghB2d8h3OZSaCzyIioMLO2qH8A8pYb8%3D; expires=Tue, 16-Aug-2016 22:11:28 GMT; path=/updatesys; domain=54.183.10.158'
	),
	'spx_init'=>array(
		'GET'=>'/spx_root/index.php/api_root/spx_init',
		'POST'=>'pd={"app_key":"ING004","spx_did":1452143,"publish":"google","device":{"gp_adid":"0635f169-675c-46a2-bd7d-5746f87a110f","android_id":"-1","ios_idfa":"-1","mac_address":"-1","platform":"android"},"info":{"app_version":"1.8.1","os_version":"4.4.4","content_version":"0191","platform":"android","device_type":"Lenovo TAB 2 A10-70F 4.4.4"}}',
		'RES'=>''
	),
	'auto_login'=>array(
		'GET'=>'/spx_account/index.php/api_account/auto_login',
		'POST'=>'pd={"app_key":"ING004","account_key":"AWUyJD%2bwHmliG5lP8QhQIyBluu3eL3X5A21bDl2Uk4zDb/qDf6hw6FalUCaOFcrLSNTetgGR98a5lf6LYJ374tq%2bDyst2Uus/iEt6tCjRR9z4uksNSSkZRw1ypr/2YwtUAOUvIIXJtW1OjDXPGOL1P%2bmA8b6b7q1QTzRaiJ1Xxc=","device_id":"-1","style":"android","account_id":"20217399","client_key":"QwB5vg04xveowr3LMKyX9oSn/oBvrY6uEkul0HCnuuwd0H%2bSXtlE5QTx5rFjaSdv6LfkKLyeYGuI8yHP4MTRkL5x4XK1xNATb0NTvGc2pDref9%2bjNjIIoh8DoTzkQjsP8pSOq78iIogrOFnPUNC/zIPkY88Zv8pQ7eUrpfxQSag="}',
		'RES'=>''
	),
	'spx_gsm'=>array(
		'GET'=>'/spx_gsm/api_game_server/serverList/ING004/google/0191/0/ru',
		'POST'=>'',
		'RES'=>''
	),
	'auto_login'=>array(
		'GET'=>'/spx_account/index.php/api_account/auto_login',
		'POST'=>'pd={"app_key":"ING004","account_key":"BzbLTZhyBP5SvIbqoPRnQVgzYI8XXTBu8wlU/7zquJaxtaetflFngvBkRZdjeEMCLiUnAOM1j1QNWH3zHa7QFz3l2q7h/bZ0fSNLfWNfCbwWxREGYHmfZQs87/k4XEMesZT5Xq/SMRIm/Nja9XvFAap5V954NHnSOaFJG4CIBAg=","device_id":"-1","style":"android","account_id":"20217399","client_key":"QWacI6hjhUsOemEgLqlVW7Go7IZ668kyLtZR678LQwdOTpfYVl9u0V%2bwBq3YSaRGW957S8ZO4d0JrwrXkIUAylTMHIW9aPcLMCvQSwAFUOe54KsWIVHbOOcewgiJXI3fgaRi4UOaDQJw9MxYhX17tV8lFkq5Ukg6MCIZ3KV2nEg="}',
		'RES'=>''
	),
	'getUserList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmLogin/getUserList',
		'POST'=>'common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":0,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:33 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&data={"app_key":"ING004","token":"SDladEFtZTdHL2dKcHdESk1jcmhWbXlYaWFkTnRSazlWNGp5cEE1SnB1UjlhbytSSU9hQmpWRGViSHVDNi8rcmFoelo2WlFIVFBuUHVtM3J1MmlMb1NXN0FKK2h0WE1JZEVCWHBlVU5ZSWZxdHdKWWxJU1V4ZC9Ea09lK3pHWXNXdjhmTmFJbGhHNHI4K2Npbk9LQzZuYk5SSmliY3F2RGkrbm5rVkVueHZabm93NUFHRjZ2RDc0M3NvZ282eXN3QlFJTGFwdVFrSFMvMUNqU3BrVUFOVVpCK2kzN3BlclVHaEMwZzZFL0Z3eXRpdlFxVmZtRkdDaUtvaXRYV0c4eXd2ZERoVmhHRzIzRnpGYlZiSU05UE9mQWF6dktaQS9rV1hFQ2IvUVYzdzVUV01tV1dBQmxMM2VRNFlqQXRJMW9CSGlHMVYvbmx3bC8yUkZJbVZ5OTJBPT0%3D","publish":"google","client_commit":"0","server_id":83}',
		'RES'=>'13'
	),
	'enterGame'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmLogin/enterGame',
		'POST'=>'ommon_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:37 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&data={"server_id":83,"spx_id":1452143,"token":"SDladEFtZTdHL2dKcHdESk1jcmhWbXlYaWFkTnRSazlWNGp5cEE1SnB1UjlhbytSSU9hQmpWRGViSHVDNi8rcmFoelo2WlFIVFBuUHVtM3J1MmlMb1NXN0FKK2h0WE1JZEVCWHBlVU5ZSWZxdHdKWWxJU1V4ZC9Ea09lK3pHWXNXdjhmTmFJbGhHNHI4K2Npbk9LQzZuYk5SSmliY3F2RGkrbm5rVkVueHZabm93NUFHRjZ2RDc0M3NvZ282eXN3QlFJTGFwdVFrSFMvMUNqU3BrVUFOVVpCK2kzN3BlclVHaEMwZzZFL0Z3eXRpdlFxVmZtRkdDaUtvaXRYV0c4eXd2ZERoVmhHRzIzRnpGYlZiSU05UE9mQWF6dktaQS9rV1hFQ2IvUVYzdzVUV01tV1dBQmxMM2VRNFlqQXRJMW9CSGlHMVYvbmx3bC8yUkZJbVZ5OTJBPT0%3D","publish":"google","client_commit":"0","app_key":"ING004","language":"ru","user_id":20893853083}',
		'RES'=>'{"error":0,"config_version":null,"session":"e62d3c4e26b39d8430f6217671a7ed84","account_id":20893853,"user_id":20893853083,"set_name":1,"user_name":"ElMar","is_developer":0,"developer_url":"","open_tapjoy":0,"global_buffs":{"r0_pro":{"value":0,"time":0},"r1_pro":{"value":0,"time":0},"r2_pro":{"value":0,"time":0},"ship_pro_speed":{"value":0,"time":0},"ship_pro":{"value":0,"time":0},"ship_pro_consume":{"value":0,"time":0},"fleet_repair":{"value":0,"time":0},"fleet_speed":{"value":0,"time":0},"free_move":{"value":0,"time":0},"peace_protocol":{"value":0,"time":0}}}'
	),
	'getUnitConfig'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUnit/getUnitConfig?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'ser_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:38 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'15'
	),
	'getUserData'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUser/getUserData?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:41 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'16'
	),
	'getPlanetInfo'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmPlanet/getPlanetInfo?sign=7875C00E309D389795E04002E1120EA5',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:42 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":"1_1_12"}&type=1',
		'RES'=>'17'
	),
	'getItemPrice'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmItem/getItemPrice?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:43 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'18'
	),
	'getRechargeList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmRecharge/getRechargeList?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'21'
	),
	'getPlanets'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmPlanet/getPlanets?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'22'
	),
	'getBlueprintsConfig'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmControl/getBlueprintsConfig?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'23'
	),
	'getActiveDescInfo'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmActive/getActiveDescInfo?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'24'
	),
	'getUnreadNewsCount'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmNews/getUnreadNewsCount?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'25'
	),
	'getRechargeList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmRecharge/getRechargeList?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'26'
	),
	'getBlacklist'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmFriendEx/getBlacklist?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'27'
	),
	'getGiftBoxCount'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmTimeActivity/getGiftBoxCount?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'28'
	),
	'getMerchant'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmMerchant/getMerchant?sign=7875C00E309D389795E04002E1120EA5',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":"1_1_12"}&type=1',
		'RES'=>'29'
	),
	'reflushRecommend'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmGiftbag/reflushRecommend?sign=1873D1B4B6124A2B8EB6DBB139A4070A',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"language":"ru","user_id":20893853083}&type=1',
		'RES'=>'30'
	),
	'getGuideState'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUser/getGuideState?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'31'
	),
	'openAllianceControl'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmControl/openAllianceControl?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:47 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'32'
	),
	'getAllInfo'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmFleet/getAllInfo?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:53 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'33'
	),
	'getEvent'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmActivity/getEvent?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'ser_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:53 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'34'
	),
	'getTaskList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmTaskEx/getTaskList?sign=9688F86AB48153238B27AC1A6F005204',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:53 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":"1_1_12","language":"en"}&type=1',
		'RES'=>'35'
	),
	'getTecsInfo'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmAllianceTec/getTecsInfo?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:53 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'36'
	),
	'getGameDataEx'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUser/getGameDataEx?sign=59E4E9FF767B53810557445E3A684B32',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:55 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":"1_1_12","item_config_version":"2016-06-05 18:45:59","count":20,"tick":-1,"language":"ru"}&type=1',
		'RES'=>'37'
	),
	'getUniverse'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUniverse/getUniverse?sign=9686B4382D2D4518731DB23D8B89F6C6',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:56 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":12,"sid":1,"language":"en","gid":1}&type=1',
		'RES'=>'38'
	),
	'getUserPlanetList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUser/getUserPlanetList?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:11:56 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'39'
	),
	'getUniverse'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUniverse/getUniverse?sign=9A8D33090A90D9713225CC8AF04DC26B',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:12:02 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":-1,"sid":2,"language":"en","gid":1}&type=1',
		'RES'=>'40'
	),
	'getUserPlanetList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUser/getUserPlanetList?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:12:02 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'42'
	),
	'getUniverse'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUniverse/getUniverse?sign=59590A319073FF2AD009B401F36F51D7',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:12:05 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":-1,"sid":3,"language":"en","gid":1}&type=1',
		'RES'=>'43'
	),
	'getUserPlanetList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUser/getUserPlanetList?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:12:05 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'44'
	),
	'getUniverse'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUniverse/getUniverse?sign=6577221391C72900B0CD35DB5A211A49',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:12:08 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":-1,"sid":4,"language":"en","gid":1}&type=1',
		'RES'=>'45'
	),
	'getUserPlanetList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUser/getUserPlanetList?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:12:08 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'46'
	),
	'getUniverse'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUniverse/getUniverse?sign=08F96C221E1F9C070F3C3D5EA256F609',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:12:10 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={"planet_id":-1,"sid":5,"language":"en","gid":1}&type=1',
		'RES'=>'47'
	),
	'getUserPlanetList'=>array(
		'GET'=>'/ING004/n/WebServer/Web/sogame/newControl/nmUser/getUserPlanetList?sign=F87A76AE2087F98F1DEDDD950E02EC8E',
		'POST'=>'user_id=20893853083&user_name=ElMar&common_data={"isJailbroken":0,"android_id":"-1","device_type_name":"Lenovo TAB 2 A10-70F 4.4.4","s_mac":"-1","user_id":20893853083,"sdk_ver":"0191","terrace_type":"google","device_detail_type":"Lenovo TAB 2 A10-70F","spx_did":1452143,"advertising_id":"0635f169-675c-46a2-bd7d-5746f87a110f","mac":"-1","language":"ru","device_uid":"-1","idfa":"-1","device_date":"Tue Aug 16 23:12:10 2016","app_type_name":"app","adId":"-1","server_id":83,"s_adid":"-1","isPirated":0,"vendorId":"-1","user_name":"ElMar","SAID":"-1","sh1dId":"-1","md5dId":"-1","pkg_version":"1.8.1","apns_token":"-1","nsuuId":"-1","device_os_version":"4.4.4"}&ex_data={}&type=1',
		'RES'=>'50'
	)
);


foreach ($URLS as $k => $v){
	echo $k." ".$v['GET']."\n";
}
?>

