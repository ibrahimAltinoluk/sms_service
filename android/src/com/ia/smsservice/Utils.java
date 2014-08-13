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

import android.app.Activity;
import android.app.AlertDialog;
import android.app.PendingIntent;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.provider.Settings.Secure;
import android.util.Log;
import android.widget.Toast;

import com.facebook.Session;
import com.facebook.model.GraphUser;

public class Utils {
	private Activity context = null;
	private Context application_context = null;
	public String SESSION_CACHE_NAME = "fbsession", USER_NAME = "username", USER_FULL_NAME = "userfullname", USER_EMAIL = "email", USER_INFO = "basic_info";

	ProgressDialog pd;

	public Utils(Activity context) {
		this.context = context;
		application_context = context.getApplicationContext();
	}

	public boolean isOnline() {
		try {
			ConnectivityManager cm = (ConnectivityManager) context.getSystemService(Context.CONNECTIVITY_SERVICE);
			NetworkInfo netInfo = cm.getActiveNetworkInfo();
			if (netInfo != null && netInfo.isConnectedOrConnecting()) {
				return true;
			}
		} catch (Exception e) {
			Toast.makeText(context, e.getMessage(), Toast.LENGTH_LONG).show();
		}
		return false;
	}

	public Utils logout() {

		new AsyncTask<Void, Void, Boolean>() {
			@Override
			protected Boolean doInBackground(Void... params) {

				try {

					SaveToken("");
					Session session = new Session(context);
					if (session != null) {
						session.closeAndClearTokenInformation();

						try {

							DefaultHttpClient httpClient = new DefaultHttpClient();

							HttpPost httpPost = new HttpPost(context.getString(R.string.cloud));

							List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(2);
							nameValuePairs.add(new BasicNameValuePair("deviceId", getDeviceId()));
							nameValuePairs.add(new BasicNameValuePair("method", "logout_device"));
							httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs, HTTP.UTF_8));
							HttpResponse httpResponse;
							httpResponse = httpClient.execute(httpPost);
							HttpEntity httpEntity = httpResponse.getEntity();
							String s = EntityUtils.toString(httpEntity);

							Log.w("logout", s);
						} catch (ClientProtocolException e) {

							e.printStackTrace();
						} catch (IOException e) {

							e.printStackTrace();
						}

					}
				} catch (Exception ex) {
					// alert(ex.getMessage());
				}

				return true;
			}

			@Override
			protected void onPreExecute() {
				// open_progress();
			};

			@Override
			protected void onPostExecute(Boolean result) {
				close_progress();
				System.exit(0);
				context.startActivity(new Intent(context, LoginActivity.class));
				context.finish();

			}
		}.execute();

		return this;
	}

	public Utils alert(Object msg) {

		AlertDialog.Builder ad = new AlertDialog.Builder(context);
		ad.setTitle(R.string.app_name).setMessage(msg.toString()).setPositiveButton("Tamam", null).show();

		return this;
	}

	public Utils open_progress() {
		if (null == pd || !pd.isShowing()) {
			pd = new ProgressDialog(context);
			pd.setMessage("Yükleniyor");
			pd.setCancelable(false);
			pd.show();
		}
		return this;
	}

	public Utils close_progress() {
		if (null != pd) {
			if (pd.isShowing()) {
				pd.dismiss();
				pd = null;
			}

		}
		return this;
	}

	public String LoadData(String key) {
		SharedPreferences sharedPreferences = application_context.getSharedPreferences(Utils.class.getSimpleName(), Context.MODE_PRIVATE);
		return sharedPreferences.getString(key, "");
	}

	public Utils SaveData(String key, String value) {
		SharedPreferences sharedPreferences = application_context.getSharedPreferences(Utils.class.getSimpleName(), Context.MODE_PRIVATE);
		SharedPreferences.Editor editor = sharedPreferences.edit();
		editor.putString(key, value);
		editor.commit();
		return this;
	}

	public Utils SaveToken(String value) {

		return SaveData(SESSION_CACHE_NAME, value);

	}

	public String LoadToken() {
		return LoadData(SESSION_CACHE_NAME);
	}

	public Utils register_on_web(GraphUser user, boolean registerc2dm) {
		try {
			final String email = user.asMap().get(USER_EMAIL).toString(), fbid = user.getId() == null ? "" : user.getId();

			DefaultHttpClient httpClient = new DefaultHttpClient();

			HttpPost httpPost = new HttpPost(context.getString(R.string.cloud));

			List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(7);
			nameValuePairs.add(new BasicNameValuePair("fbid", fbid));
			nameValuePairs.add(new BasicNameValuePair("title", "Mobile"));
			nameValuePairs.add(new BasicNameValuePair("token", LoadToken()));
			nameValuePairs.add(new BasicNameValuePair("method", "register_member"));
			nameValuePairs.add(new BasicNameValuePair("from", "device"));
			nameValuePairs.add(new BasicNameValuePair("email", email));
			nameValuePairs.add(new BasicNameValuePair("name", user.getFirstName() + " " + user.getLastName()));
			nameValuePairs.add(new BasicNameValuePair("deviceId", getDeviceId()));
			httpPost.setEntity(new UrlEncodedFormEntity(nameValuePairs, HTTP.UTF_8));
			HttpResponse httpResponse;
			httpResponse = httpClient.execute(httpPost);
			HttpEntity httpEntity = httpResponse.getEntity();
			String s = EntityUtils.toString(httpEntity);

			if (registerc2dm) {
				this.register_c2dm();
			}
		} catch (ClientProtocolException e) {

			e.printStackTrace();
		} catch (IOException e) {

			e.printStackTrace();
		}
		return this;
	}

	public Utils register_c2dm() {

		// Push Notification service start
		Intent registrationIntent = new Intent("com.google.android.c2dm.intent.REGISTER");
		registrationIntent.putExtra("app", PendingIntent.getBroadcast(context, 0, new Intent(), 0));
		registrationIntent.putExtra("sender", context.getResources().getString(R.string.sender_id));

		context.startService(registrationIntent);
		return this;

	}

	public String getDeviceId() {
		return Secure.getString(context.getContentResolver(), Secure.ANDROID_ID);
	}

	public Utils setContext(Activity context) {
		this.context = context;
		return this;
	}

	public Context getContext() {
		return this.context;
	}

}
