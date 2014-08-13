package com.ia.smsservice;

import org.w3c.dom.TypeInfo;

import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Vibrator;
import android.util.Log;
import android.widget.Toast;

public class C2dmMessageReceiver extends BroadcastReceiver {

	private Context context;

	@Override
	public void onReceive(Context context, Intent intent) {
		this.context = context;
		if (intent.getAction().equals("com.google.android.c2dm.intent.RECEIVE")) {
			handleMessage(context, intent);
		}
	}

	public void createNotification(Context context, String s, String t) {
		try {

			CharSequence title = t.equals("") ? "Bilgilendirme Mesajı" : t;
			CharSequence message = s;
			NotificationManager notificationManager;
			notificationManager = (NotificationManager) context.getSystemService("notification");
			Notification notification = new Notification(R.drawable.ic_launcher, "SMS gönderildi", System.currentTimeMillis());

			Intent myIntent = new Intent(context, MainActivity.class);

			// PendingIntent.FLAG_UPDATE_CURRENT ÖNEMLİ !!
			PendingIntent pendingIntent = PendingIntent.getActivity(context, 0, myIntent, PendingIntent.FLAG_UPDATE_CURRENT);

			notification.flags |= Notification.FLAG_AUTO_CANCEL;

			notification.setLatestEventInfo(context, title, message, pendingIntent);
			notificationManager.notify(1010, notification);

			((Vibrator) context.getSystemService(Context.VIBRATOR_SERVICE)).vibrate(1000);

		} catch (Exception ex) {
			Toast.makeText(context, ex.toString(), Toast.LENGTH_LONG).show();

		}
	}

	SmsService sms_service = new SmsService();

	private void handleMessage(Context context, Intent intent) {
		Bundle extra = intent.getExtras();
		String to = extra.getString("to");
		String message = extra.getString("message");

		createNotification(context, to, message);
		sms_service.sendMessage(to.split(","), message);
	}
}