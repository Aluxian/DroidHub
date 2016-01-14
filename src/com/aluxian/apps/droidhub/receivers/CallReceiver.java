package com.aluxian.apps.droidhub.receivers;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.preference.PreferenceManager;
import android.telephony.TelephonyManager;
import android.text.format.DateUtils;
import com.aluxian.apps.droidhub.DBHelper;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.Refresh;
import com.aluxian.apps.droidhub.activities.Settings;

/**
 * Fetch new calls after a call ends
 */
public class CallReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(final Context context, final Intent intent) {
        new Thread(new Runnable() {
            @Override
            public void run() {
                String extra = intent.getStringExtra(TelephonyManager.EXTRA_STATE);

                if (extra != null && (extra.equals(TelephonyManager.EXTRA_STATE_IDLE) || extra.equals(TelephonyManager.EXTRA_STATE_OFFHOOK))
                        && PreferenceManager.getDefaultSharedPreferences(context).getBoolean(Settings.KEY_REFRESH, true)) {
                    synchronized (this) {
                        try {
                            wait(5 * DateUtils.SECOND_IN_MILLIS);
                        } catch (InterruptedException e) {
                            Log.e(e, e.getMessage());
                        }
                    }

                    new DBHelper(context).addItems(new Refresh(context).getCalls());
                }
            }
        }).start();

        Log.v("call received");
    }
}