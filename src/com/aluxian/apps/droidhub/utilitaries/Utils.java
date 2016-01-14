package com.aluxian.apps.droidhub.utilitaries;

import android.app.AlarmManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.content.pm.Signature;
import android.net.Uri;
import android.provider.BaseColumns;
import android.text.TextUtils;
import android.text.format.DateUtils;
import android.util.Base64;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.receivers.RefreshReceiver;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.concurrent.TimeUnit;

/**
 * Common methods used in other classes
 */
public class Utils {
    private static final long BACKGROUND_REFRESH_INTERVAL = 2 * DateUtils.HOUR_IN_MILLIS;

    /**
     * Start a phone call
     */
    public static void doCall(Context context, String number) {
        context.startActivity(new Intent(Intent.ACTION_CALL, Uri.parse("tel:" + number)));
    }

    /**
     * Schedule an alarm to perform background refresh
     */
    public static void setBackgroundRefresh(Context context) {
        AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);
        PendingIntent pendingIntent = PendingIntent.getBroadcast(context, 0, new Intent(context, RefreshReceiver.class), 0);

        alarmManager.cancel(pendingIntent);
        alarmManager.setInexactRepeating(AlarmManager.RTC_WAKEUP, System.currentTimeMillis() + 10 * DateUtils.MINUTE_IN_MILLIS, BACKGROUND_REFRESH_INTERVAL, pendingIntent);
    }

    /**
     * Cancel the alarm that performs background refresh
     */
    public static void cancelBackgroundRefresh(Context context) {
        AlarmManager alarmManager = (AlarmManager) context.getSystemService(Context.ALARM_SERVICE);
        PendingIntent pendingIntent = PendingIntent.getBroadcast(context, 0, new Intent(context, RefreshReceiver.class), 0);

        alarmManager.cancel(pendingIntent);
    }

    /**
     * Transform time in ms to Xh Ym Zs
     */
    public static String getHMS(long duration) {
        long hours = TimeUnit.SECONDS.toHours(duration);
        long minutes = TimeUnit.SECONDS.toMinutes(duration) - hours * 60;
        long seconds = TimeUnit.SECONDS.toSeconds(duration) - minutes * 60 - hours * 60 * 60;

        String hms = "";

        if (hours > 0) {
            hms += hours + (hours == 1 ? " hour " : " hours ");
        }

        if (minutes > 0) {
            hms += minutes + (minutes == 1 ? " min " : " mins ");
        }

        if (seconds > 0) {
            hms += seconds + (seconds == 1 ? " sec " : " secs ");
        }

        return hms;
    }

    /**
     * Return first string that is not null nor empty
     */
    public static String firstTruthy(String... strings) {
        for (String str : strings) {
            if (!TextUtils.isEmpty(str)) {
                return str;
            }
        }

        return null;
    }

    /**
     * Return first object that is not null
     */
    public static Object firstTruthy(Object... args) {
        for (Object arg : args) {
            if (arg != null) {
                return arg;
            }
        }

        return null;
    }

    /**
     * Additional calls table columns
     */
    public static class CallsColumns {
        public static final String CONTENT_URI = "content://sms/inbox";
        public static final String NORMALIZED_NUMBER = "normalized_number";
        public static final String FORMATTED_NUMBER = "formatted_number";
    }

    /**
     * SMS table columns
     */
    public static class SMSColumns implements BaseColumns {
        public static final String ADDRESS = "address";
        public static final String BODY = "body";
        public static final String DATE = "date";
        public static final int MESSAGE_TYPE_ALL = 0;
        public static final int MESSAGE_TYPE_DRAFT = 3;
        public static final int MESSAGE_TYPE_INBOX = 1;
        public static final int MESSAGE_TYPE_OUTBOX = 4;
        public static final int MESSAGE_TYPE_SENT = 2;
        public static final String PERSON = "person";
        public static final String PERSON_ID = "person";
        public static final String READ = "read";
        public static final String REPLY_PATH_PRESENT = "reply_path_present";
        public static final String SERVICE_CENTER = "service_center";
        public static final String STATUS = "status";
        public static final String SUBJECT = "subject";
        public static final String THREAD_ID = "thread_id";
        public static final String TYPE = "type";
    }
}