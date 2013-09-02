/**
 * デスクトップ通知APIラッパー
 * 
 * 	if (NotificationAPI.checkPermission() == 0){
 * 		NotificationAPI.requestPermission(function(permission){
 * 			if (permission == 0){
 * 			
 * 			}else{
 * 			
 * 			}
 * 		});
 * 	}
 */
var NotificationAPI = null;

if (window.webkitNotifications){
	//Chrome or Safari
	NotificationAPI = {
		createNotification: function(icon,title,body){
			return webkitNotifications.createNotification(icon,title,body);
		},
		checkPermission: function(){
			return webkitNotifications.checkPermission();
		},
		requestPermission: function(callback){
			if (typeof(callback) != 'function'){
				callback = function(){};
			}
			webkitNotifications.requestPermission(function(){
				callback(NotificationAPI.checkPermission());
			});
		}
	};
}else if (window.Notification){
	//W3C Web Notifications
	//http://www.w3.org/TR/notifications/

	//Firefoxが実装
	//http://logroid.blogspot.jp/2013/06/firefox-html5-notifications-api.html
	//設定方法: http://logroid.blogspot.jp/2013/06/firefox-html5-notifications-setting.html
	NotificationAPI = {
		createNotification: function(icon,title,body){
			var popup = new Notification(title,{
				icon: icon,
				body: body
			});
			popup.show = function(){};
			popup.cancel = function(){
				popup.close();
			};
			return popup;
		},
		checkPermission: function(){
			switch (Notification.permission){
				case 'granted':
					return 0;
				case 'default':
					return 1;
				case 'denied':
					return 2;
			}
		},
		requestPermission: function(callback){
			if (typeof(callback) != 'function'){
				callback = function(){};
			}
			Notification.requestPermission(function(permission){
				callback(NotificationAPI.checkPermission());
			});
		}
	};
}
