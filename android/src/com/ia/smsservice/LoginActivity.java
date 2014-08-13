package com.ia.smsservice;

import java.util.Arrays;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.StrictMode;
import android.support.v4.app.FragmentActivity;
import android.util.Log;
import android.view.View;
import android.widget.Button;

import com.facebook.AccessToken;
import com.facebook.Request;
import com.facebook.Response;
import com.facebook.Session;
import com.facebook.SessionState;
import com.facebook.model.GraphUser;

public class LoginActivity extends FragmentActivity {

	Context context;
	Utils utils;
	Activity activity;
	Button fb_login;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.login);

		context = activity = this;
		utils = new Utils(activity);

		fb_login = (Button) findViewById(R.id.btn_login);
		fb_login.setVisibility(View.INVISIBLE);
		fb_login.setOnClickListener(logintrigger);

		StrictMode.ThreadPolicy policy = new StrictMode.ThreadPolicy.Builder().permitAll().build();
		StrictMode.setThreadPolicy(policy);

		ifSessionActiveThenContinueIt();

	}

	public void ifSessionActiveThenContinueIt() {
		Session session = new Session(context);
		AccessToken at;
		at = AccessToken.createFromExistingAccessToken(utils.LoadToken(), null, null, null, null);
		if (!utils.LoadToken().equals("")) {
			utils.open_progress();
			Session.openActiveSessionWithAccessToken(context, at, session_call_back);

		} else {
			fb_login.setVisibility(View.VISIBLE);
		}
	}

	View.OnClickListener logintrigger = new View.OnClickListener() {

		@Override
		public void onClick(View v) {
			Session session = new Session(context);
			Session.OpenRequest request = new Session.OpenRequest(activity).setPermissions(Arrays.asList(utils.USER_EMAIL, utils.USER_INFO));
			request.setCallback(session_call_back);
			Session.setActiveSession(session);
			session.openForRead(request);

		}
	};
	Session.StatusCallback session_call_back = new Session.StatusCallback() {
		@Override
		public void call(final Session session, SessionState state, Exception exception) {

			Session.setActiveSession(session);
			if (session.isOpened()) {
				Request.executeMeRequestAsync(session, new Request.GraphUserCallback() {
					@Override
					public void onCompleted(final GraphUser user, final Response response) {

						if (user != null) {
							startActivity(new Intent(context, MainActivity.class));

							Log.w("bilgiler", user.getInnerJSONObject().toString());
							utils.SaveToken(session.getAccessToken()).SaveData(utils.USER_NAME, user.getFirstName()).SaveData(utils.USER_FULL_NAME, user.getFirstName() + " " + user.getLastName())
									.register_on_web(user, true);

							finish();
						}
					}
				});

			}
			utils.close_progress();
		}
	};

	@Override
	public void onActivityResult(int requestCode, int resultCode, Intent data) {
		super.onActivityResult(requestCode, resultCode, data);
		Session.getActiveSession().onActivityResult(this, requestCode, resultCode, data);
	}

}
