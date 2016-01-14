package com.aluxian.apps.droidhub.receivers;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.text.format.DateUtils;
import com.aluxian.apps.droidhub.DBHelper;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.Refresh;

/**
 * Fetch new sms messages
 */
public class SMSReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(final Context context, final Intent intent) {
        new Thread(new Runnable() {
            @Override
            public void run() {
                synchronized (this) {
                    try {
                        wait(5 * DateUtils.SECOND_IN_MILLIS);
                    } catch (InterruptedException e) {
                        Log.e(e, e.getMessage());
                    }
                }

                new DBHelper(context).addItems(new Refresh(context).getMessages());
            }
        }).start();

        Log.v("sms received");
    }
}