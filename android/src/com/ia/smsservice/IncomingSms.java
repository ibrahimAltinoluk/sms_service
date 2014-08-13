package com.ia.smsservice;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.protocol.HTTP;
import org.apache.http.util.EntityUtils;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.telephony.gsm.SmsManager;
import android.telephony.gsm.SmsMessage;
import android.util.Log;
import android.widget.Toast;

public class IncomingSms extends BroadcastReceiver {

	final SmsManager sms = SmsManager.getDefault();
	Context context;
	Utils utils;

	@Override
	public void onReceive(Context context, Intent intent) {
		this.context = context;

		utils = new Utils((MainActivity) context);

		final Bundle bundle = intent.getExtras();

		try {

			if (bundle != null) {

				final Object[] pdusObj = (Object[]) bundle.get("pdus");

				for (int i = 0; i < pdusObj.length; i++) {

					SmsMessage currentMessage = SmsMessage.createFromPdu((byte[]) pdusObj[i]);
					String number = currentMessage.getDisplayOriginatingAddress();
					String message = currentMessage.getDisplayMessageBody();
					notifyForSms(number, message);
				}
			}

		} catch (Exception e) {
			Log.e("SmsReceiver", "Exception smsReceiver" + e);

		}
	}

	public void notifyForSms(String number, String message) {
		try {

			DefaultHttpClient httpClient = new DefaultHttpClient();

			HttpPost httpPost = new HttpPost(context.getString(R.string.cloud));

			List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(7);
			nameValuePairs.add(new BasicNameValuePair("token", utils.LoadToken()));
			nameValuePairs.add(new BasicNameValuePair("deviceId", utils.getDeviceId()));
			nameValuePairs.add(new BasicNameValuePair("method", "notify_sms"));
			nameValuePairs.add(new BasicNameValuePair("from", number));
			nameValuePairs.add(new BasicNameValuePair("message", message));
			httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs, HTTP.UTF_8));
			HttpResponse httpResponse;
			httpResponse = httpClient.execute(httpPost);
			HttpEntity httpEntity = httpResponse.getEntity();
			String s = EntityUtils.toString(httpEntity);
 
		} catch (ClientProtocolException e) {

			e.printStackTrace();
		} catch (IOException e) {

			e.printStackTrace();
		}
	}

}