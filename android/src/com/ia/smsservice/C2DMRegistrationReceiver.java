package com.ia.smsservice;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.List;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;
import android.provider.Settings;
import android.util.Log;

public class C2DMRegistrationReceiver extends BroadcastReceiver {
	Context context;

	@Override
	public void onReceive(Context context, Intent intent) {
		String action = intent.getAction();
		this.context = context;

		if ("com.google.android.c2dm.intent.REGISTRATION".equals(action)) {
			final String registrationId = intent.getStringExtra("registration_id");

			String deviceId = Settings.Secure.getString(context.getContentResolver(), Settings.Secure.ANDROID_ID);
			sendRegistrationIdToServer(deviceId, registrationId);
		}
	}

	public void sendRegistrationIdToServer(String di, String ri) {

		final String deviceId = di;
		final String registrationId = ri;

		Thread t = new Thread(new Runnable() {
			@Override
			public void run() {
				HttpClient client = new DefaultHttpClient();
				HttpPost post = new HttpPost(context.getResources().getString(R.string.cloud));
				try {
					List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(3);
					nameValuePairs.add(new BasicNameValuePair("method", "register_c2dm"));
 					nameValuePairs.add(new BasicNameValuePair("deviceId", deviceId));
					nameValuePairs.add(new BasicNameValuePair("register", registrationId));

					post.setEntity(new UrlEncodedFormEntity(nameValuePairs));
					HttpResponse response = client.execute(post);
					BufferedReader rd = new BufferedReader(new InputStreamReader(response.getEntity().getContent()));

				} catch (IOException e) {
				}
			}
		});

		t.start();

	}

}