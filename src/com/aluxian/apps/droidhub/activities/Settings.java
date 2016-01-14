package com.aluxian.apps.droidhub.activities;

import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Bundle;
import android.preference.CheckBoxPreference;
import android.preference.Preference;
import android.preference.PreferenceActivity;
import android.view.MenuItem;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.R;
import com.aluxian.apps.droidhub.utilitaries.Utils;
import com.google.analytics.tracking.android.EasyTracker;

/**
 * Settings activity for user configurable options
 */
@SuppressWarnings("deprecation")
public class Settings extends PreferenceActivity {
    /**
     * Preference keys for settings
     */
    public static final String KEY_SEND_FEEDBACK = "SEND_FEEDBACK";
    public static final String KEY_REFRESH = "BACKGROUND_REFRESH";
    public static final String KEY_HIDE_UNKNOWN = "HIDE_UNKNOWN";
    public static final String KEY_APP_VERSION_INFO = "APP_VERSION_INFO";

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        addPreferencesFromResource(R.xml.settings);

        final CheckBoxPreference refreshPref = (CheckBoxPreference) findPreference(KEY_REFRESH);
        refreshPref.setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {
            @Override
            public boolean onPreferenceChange(Preference preference, Object newValue) {
                if ((Boolean) newValue) {
                    Utils.setBackgroundRefresh(getApplicationContext());
                } else {
                    Utils.cancelBackgroundRefresh(getApplicationContext());
                }

                return true;
            }
        });

        Preference feedbackPref = findPreference(KEY_SEND_FEEDBACK);
        feedbackPref.setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(Preference preference) {
                try {
                    startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse("market://details?id=com.aluxian.apps.droidhub")));
                } catch (ActivityNotFoundException e) {
                    startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse("https://play.google.com/store/apps/details?id=com.aluxian.apps.droidhub")));
                }

                return true;
            }
        });

        try {
            String versionName = getPackageManager().getPackageInfo(getPackageName(), 0).versionName;
            findPreference(KEY_APP_VERSION_INFO).setSummary("Droid Hub v" + versionName);
        } catch (PackageManager.NameNotFoundException e) {
            Log.e(e, e.getMessage());
        }
    }

    @Override
    protected void onStart() {
        super.onStart();

        if (Log.GANALYTICS) {
            EasyTracker.getInstance(this).activityStart(this);
        }
    }

    @Override
    protected void onStop() {
        super.onStop();

        if (Log.GANALYTICS) {
            EasyTracker.getInstance(this).activityStop(this);
        }
    }

    @Override
    public void onBackPressed() {
        startActivity(new Intent(getApplicationContext(), MainActivity.class));
        finish();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case android.R.id.home:
                startActivity(new Intent(getApplicationContext(), MainActivity.class));
                return true;
        }

        return super.onOptionsItemSelected(item);
    }
}