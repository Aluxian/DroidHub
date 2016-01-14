package com.aluxian.apps.droidhub.models;

import android.graphics.Color;
import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.Date;

/**
 * Item object used to hold data for each item in the activity list
 */
public class Item {
    /**
     * Date formatters
     */
    public static final SimpleDateFormat formatHeaderDate = new SimpleDateFormat("EEE, MMMM d");

    /**
     * Fields
     */
    public String headerDate;
    public String rowId;
    public String itemId;
    public String senderId;
    public Date date;
    public Type type;
    public String title;
    public String body;
    public JSONObject extra;

    public Item(Date date) {
        this.headerDate = formatHeaderDate.format(date);
        this.date = date;
        this.type = Type.DATE;
    }

    public Item(String itemId, String senderId, Date date, Type type, String title, String body, JSONObject extra) {
        this(null, itemId, senderId, date, type, title, body, extra);
    }

    public Item(String rowId, String itemId, String senderId, Date date, Type type, String title, String body, JSONObject extra) {
        this.rowId = rowId;
        this.itemId = itemId;
        this.senderId = senderId;
        this.date = date;
        this.type = type;
        this.title = title;
        this.body = body;
        this.extra = extra;
    }

    /**
     * Enum for item types
     */
    public enum Type {
        ALL(0, "All", null),
        DATE(1, null, "#26c0b8", "list_date"),
        CALL(2, "Call Logs", "#2f8dcc"),
        SMS(3, "SMS Messages", "#70b818"),
        TW_MN(4, "Twitter Mentions", "#47B9FF"),
        TW_DM(5, "Twitter DMs", "#47B9FF"),
        GMAIL(6, "Gmail", "#f39c12");

        public int id, color;
        public String accName, layout;

        private Type(int id, String accName, String color) {
            this(id, accName, color, "list_item");
        }

        private Type(int id, String accName, String color, String layout) {
            this.id = id;
            this.accName = accName;
            this.layout = layout;

            if (color != null) {
                this.color = Color.parseColor(color);
            }
        }
    }

    /**
     * Extra object fields
     */
    public static class Extra {
        public static final String FULL_TITLE = "fullTitle";
        public static final String NUMBER = "number";
        public static final String CALL_TYPE = "callType";
        public static final String CALL_DURATION = "callDuration";
        public static final String MISSED_CALLS_NO = "missedCallsNo";
        public static final String PHOTO_URI = "photoUri";
        public static final String SYSTEM_MSG = "systemMsg";
    }
}