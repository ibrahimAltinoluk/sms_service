package com.ia.smsservice;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.AlertDialog.Builder;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.StrictMode;
import android.provider.ContactsContract;
import android.provider.ContactsContract.CommonDataKinds.Phone;
import android.text.TextUtils;
import android.util.Log;
import android.view.KeyEvent;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;

public class MainActivity extends Activity {
	Context context;
	Activity activity;
	TextView txt;
	Button btn_logout, btn_sync, btn_sent;
	Utils utils;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);

		StrictMode.ThreadPolicy policy = new StrictMode.ThreadPolicy.Builder().permitAll().build();
		StrictMode.setThreadPolicy(policy);

		context = activity = this;
		utils = new Utils(activity);

		// LoginOrFuckOff();

		setContentView(R.layout.activity_main);

		btn_logout = (Button) findViewById(R.id.btn_logout);
		btn_sent = (Button) findViewById(R.id.btn_sent);
		btn_sync = (Button) findViewById(R.id.btn_sync);
		txt = (TextView) findViewById(R.id.txt);

		txt.setText(utils.LoadData(utils.USER_FULL_NAME));

		btn_sync.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				try {
					syncContacts();
				} catch (JSONException e) {
					txt.setText(e.getMessage());
				}
			}
		});

		btn_sent.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				startActivity(new Intent(context, SentActivity.class));
			}
		});
		btn_logout.setOnClickListener(new View.OnClickListener() {

			@Override
			public void onClick(View v) {
				utils.logout();
			}
		});

	}

	@Override
	protected void onResume() {
		super.onResume();
		// LoginOrFuckOff();
	}

	private void LoginOrFuckOff() {
		if (utils.LoadToken().equals("")) {
			AlertDialog.Builder ad = new AlertDialog.Builder(context);
			ad.setTitle("Login Olmalısınız").setMessage("Ne yapmak istersiniz?").setPositiveButton("FB Login", new DialogInterface.OnClickListener() {
				@Override
				public void onClick(DialogInterface dialog, int which) {
					startActivity(new Intent(context, LoginActivity.class));
				}
			}).setNegativeButton("Çık", new DialogInterface.OnClickListener() {
				@Override
				public void onClick(DialogInterface dialog, int which) {
					System.exit(0);
				}
			}).show();
		}

	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.main, menu);
		return true;
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		if (item.getItemId() == R.id.sync) {
		}
		try {
			syncContacts();
		} catch (JSONException e) {
			txt.setText(e.getMessage());
		}

		return super.onOptionsItemSelected(item);
	}

	private void syncContacts() throws JSONException {
		Cursor c_phone = context.getContentResolver().query(ContactsContract.CommonDataKinds.Phone.CONTENT_URI, new String[] { Phone._ID, Phone.DISPLAY_NAME, Phone.NUMBER }, null, null,
				Phone.DISPLAY_NAME + " ASC");

		String[] json_as_string = new String[c_phone.getCount()];

		c_phone.moveToFirst();
		do {
			json_as_string[c_phone.getPosition()] = "{title:'" + c_phone.getString(1).replace("'", "\'") + "',phone:'" + c_phone.getString(2) + "'}";
		} while (c_phone.moveToNext());

		JSONObject js = new JSONObject("{contacts:[" + TextUtils.join(",", json_as_string) + "]}");
		new SyncService().doInBackground(js.toString());

	}

	@Override
	public boolean onKeyDown(int keyCode, KeyEvent event) {
		if (KeyEvent.KEYCODE_BACK == keyCode) {

			System.exit(0);
		}

		return true;

	}

	private class SyncService extends AsyncTask<String, String, String> {

		@Override
		protected String doInBackground(String... params) {

			HttpClient httpclient = new DefaultHttpClient();
			HttpPost httppost = new HttpPost(getString(R.string.cloud));

			try {
				// Add your data
				List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(2);
				nameValuePairs.add(new BasicNameValuePair("token", utils.LoadToken()));
				nameValuePairs.add(new BasicNameValuePair("data", params[0]));
				nameValuePairs.add(new BasicNameValuePair("method", "sync"));
				httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs, "UTF-8"));

				// Execute HTTP Post Request
				HttpResponse response = httpclient.execute(httppost);

				ByteArrayOutputStream out = new ByteArrayOutputStream();
				response.getEntity().writeTo(out);
				out.close();
				String resString = out.toString();

				return resString.toString();
			} catch (ClientProtocolException e) {
				Log.e("iiiii", "iiii" + e.getMessage());
			} catch (IOException e) {
				Log.e("iiiii", "iiii" + e.getMessage());
			}

			return "Hata ?";
		}

		protected void onPostExecute(String result) {
			// Toast.makeText(context, "bitti", 1).show();
		}

	}

}
