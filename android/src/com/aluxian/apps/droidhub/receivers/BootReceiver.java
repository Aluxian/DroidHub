package com.aluxian.apps.droidhub.receivers;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.preference.PreferenceManager;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.utilitaries.Utils;
import com.aluxian.apps.droidhub.activities.Settings;

/**
 * Schedule an alarm for background refresh
 */
public class BootReceiver extends BroadcastReceiver {

    @Override
    public void onReceive(Context context, Intent intent) {
        if (PreferenceManager.getDefaultSharedPreferences(context).getBoolean(Settings.KEY_REFRESH, true)) {
            Utils.setBackgroundRefresh(context);
        }

        Log.v("boot received");
    }
}