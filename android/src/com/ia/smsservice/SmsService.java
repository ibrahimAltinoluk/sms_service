package com.ia.smsservice;

import java.util.ArrayList;

import android.telephony.gsm.SmsManager;

public class SmsService {

	public boolean sendMessage(String[] to, String message) {
		
		SmsManager sms = SmsManager.getDefault();
		ArrayList<String> parts = sms.divideMessage(message);

		for (String t : to) {
			sms.sendMultipartTextMessage(t, null, parts, null, null);
		}

		return true;

	}
}
