package com.aluxian.apps.droidhub.receivers;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.preference.PreferenceManager;
import android.text.format.DateUtils;
import com.aluxian.apps.droidhub.DBHelper;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.Refresh;
import com.aluxian.apps.droidhub.activities.Settings;

/**
 * Called by the alarm manager to refresh
 */
public class RefreshReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(final Context context, Intent intent) {
        new Thread(new Runnable() {
            @Override
            public void run() {
                if (PreferenceManager.getDefaultSharedPreferences(context).getBoolean(Settings.KEY_REFRESH, true)) {
                    synchronized (this) {
                        try {
                            wait(5 * DateUtils.SECOND_IN_MILLIS);
                        } catch (InterruptedException e) {
                            Log.e(e, e.getMessage());
                        }
                    }

                    new DBHelper(context).addItems(new Refresh(context).getAll(null));
                }
            }
        }).start();

        Log.v("refresh received");
    }
}