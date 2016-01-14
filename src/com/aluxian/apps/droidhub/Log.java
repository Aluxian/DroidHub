package com.aluxian.apps.droidhub;

import com.crittercism.app.Crittercism;

/**
 * Custom logging helper
 */
public class Log {
    /**
     * Logging toggle
     */
    public static boolean LOGS = true;

    /**
     * Crittercism reporting toggle
     */
    public static boolean CRITTERCISM = false;

    /**
     * Google Analytics reporting toggle
     */
    public static boolean GANALYTICS = false;

    /**
     * Toggle strict mode
     */
    public static boolean STRICT_MODE = false;

    /**
     * Logging tag
     */
    public static String TAG = "DroidHub";

    /**
     * Verbose level
     */
    public static void v(Object... messages) {
        if (LOGS) {
            String logMessage = "";

            for (Object message : messages) {
                logMessage += String.valueOf(message) + " ";
            }

            android.util.Log.v(TAG, logMessage);
        }
    }

    /**
     * Debug level
     */
    public static void d(Object... messages) {
        if (LOGS) {
            String logMessage = "";

            for (Object message : messages) {
                logMessage += String.valueOf(message) + " ";
            }

            android.util.Log.d(TAG, logMessage);
        }
    }

    /**
     * Error level
     * Also send the exceptions to crittercism
     */
    public static void e(Exception exception, Object... messages) {
        if (CRITTERCISM) {
            Crittercism.logHandledException(exception);
        }

        if (LOGS) {
            String logMessage = "";

            for (Object message : messages) {
                logMessage += String.valueOf(message) + " ";
            }

            android.util.Log.e(TAG, logMessage);
        }
    }
}
