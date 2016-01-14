package com.aluxian.apps.droidhub;

import android.content.ContentResolver;
import android.content.Context;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.net.Uri;
import android.preference.PreferenceManager;
import android.provider.CallLog;
import android.provider.ContactsContract;
import android.telephony.PhoneNumberUtils;
import android.text.TextUtils;
import com.aluxian.apps.droidhub.activities.Settings;
import com.aluxian.apps.droidhub.models.Account;
import com.aluxian.apps.droidhub.models.Item;
import com.aluxian.apps.droidhub.utilitaries.Utils;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;

/**
 * Fetch new activity
 */
public class Refresh {
    private DBHelper dbHelper;
    private ContentResolver contentResolver;
    private SharedPreferences prefs;

    private Cursor contacts;
    private HashMap<String, ContactData> contactsData;
    private ArrayList<String> contactsWithMultipleNumbers;

    private static ArrayList<String> contactsColumns = new ArrayList<String>();

    static {
        contactsColumns.add(ContactsContract.Data.DATA1);
        contactsColumns.add(ContactsContract.Data.DATA4);
        contactsColumns.add(ContactsContract.Data.PHOTO_THUMBNAIL_URI);
        contactsColumns.add(ContactsContract.Data.DISPLAY_NAME);
    }

    public Refresh(Context context) {
        this.dbHelper = new DBHelper(context);
        this.contentResolver = context.getContentResolver();
        this.prefs = PreferenceManager.getDefaultSharedPreferences(context);
    }

    /**
     * Return the contacts data cursor and recreate it if it's null
     */
    private Cursor getContactsCursor() {
        if (contacts != null) {
            return contacts;
        }

        String contactsSelection = ContactsContract.Data.HAS_PHONE_NUMBER + " = 1 AND "
                + ContactsContract.Data.MIMETYPE + " = '" + ContactsContract.CommonDataKinds.Phone.CONTENT_ITEM_TYPE + "'";

        return contacts = contentResolver.query(ContactsContract.Data.CONTENT_URI, contactsColumns.toArray(new String[contactsColumns.size()]), contactsSelection, null, null);
    }

    /**
     * Return a hashmap with phone number as key and photoUri, displayName as value
     */
    private ContactData getContactData(String forNumber) {
        if (contactsData != null) {
            return contactsData.get(forNumber);
        }

        HashMap<String, ContactData> contactsData = new HashMap<String, ContactData>();
        Cursor c = getContactsCursor();

        if (c.moveToFirst()) {
            do {
                String data1 = c.getString(contactsColumns.indexOf(ContactsContract.Data.DATA1));
                String data4 = c.getString(contactsColumns.indexOf(ContactsContract.Data.DATA4));
                String photoUri = c.getString(contactsColumns.indexOf(ContactsContract.Data.PHOTO_THUMBNAIL_URI));
                String displayName = c.getString(contactsColumns.indexOf(ContactsContract.Data.DISPLAY_NAME));

                String number1 = data1 != null ? PhoneNumberUtils.formatNumber(data1.replaceAll("\\s", "")) : "";
                String number2 = data4 != null ? PhoneNumberUtils.formatNumber(data4.replaceAll("\\s", "")) : "";

                if (PhoneNumberUtils.isWellFormedSmsAddress(number1) || PhoneNumberUtils.isWellFormedSmsAddress(number2)) {
                    ContactData contactData = new ContactData(data1, photoUri, displayName);
                    contactsData.put(data1, contactData);
                    contactsData.put(data4, contactData);
                }
            } while (c.moveToNext());
        }

        this.contactsData = contactsData;
        return contactsData.get(forNumber);
    }

    /**
     * A list with the names of contacts that have more than one phone number
     */
    private ArrayList<String> getContactsWithMultipleNumbers() {
        if (contactsWithMultipleNumbers != null) {
            return contactsWithMultipleNumbers;
        }

        ArrayList<String> contactsWithMultipleNumbers = new ArrayList<String>();
        Cursor c = getContactsCursor();

        if (c.moveToFirst()) {
            ArrayList<String> contactNames = new ArrayList<String>();

            do {
                String displayName = c.getString(contactsColumns.indexOf(ContactsContract.Data.DISPLAY_NAME));

                if (contactNames.contains(displayName)) {
                    contactsWithMultipleNumbers.add(displayName);
                } else {
                    contactNames.add(displayName);
                }
            } while (c.moveToNext());
        }

        return this.contactsWithMultipleNumbers = contactsWithMultipleNumbers;
    }

    /**
     * Get all new activity
     */
    public ArrayList<Item> getAll(ArrayList<Account> accounts) {
        ArrayList<Item> items = new ArrayList<Item>();

        if (accounts == null) {
            accounts = dbHelper.getAccounts();
        }

        for (Account account : accounts) {
            if (account.type == Item.Type.CALL) {
                items.addAll(getCalls());
            }

            if (account.type == Item.Type.SMS) {
                items.addAll(getMessages());
            }

            // TODO: get other accounts activity
        }

        return items;
    }

    /**
     * Get new calls from the calls content provider
     */
    public ArrayList<Item> getCalls() {
        ArrayList<Item> items = new ArrayList<Item>();
        boolean hideUnknown = prefs.getBoolean(Settings.KEY_HIDE_UNKNOWN, true);
        dbHelper.deleteData(Item.Type.CALL);

        ArrayList<String> columns = new ArrayList<String>();
        columns.add(CallLog.Calls._ID);
        columns.add(CallLog.Calls.DATE);
        columns.add(CallLog.Calls.TYPE);
        columns.add(CallLog.Calls.NUMBER);
        columns.add(CallLog.Calls.DURATION);
        columns.add(Utils.CallsColumns.NORMALIZED_NUMBER);
        columns.add(Utils.CallsColumns.FORMATTED_NUMBER);

        Cursor c = contentResolver.query(CallLog.Calls.CONTENT_URI, columns.toArray(new String[columns.size()]), null, null, CallLog.Calls._ID + " ASC");

        if (c == null) {
            return items;
        } else if (!c.moveToFirst()) {
            c.close();
            return items;
        }

        do {
            try {
                String title, body;
                String number = c.getString(columns.indexOf(CallLog.Calls.NUMBER));

                if (number == null || number.length() <= 2) {
                    continue;
                }

                String normalizedNumber = c.getString(columns.indexOf(Utils.CallsColumns.NORMALIZED_NUMBER));
                String cachedFormattedNumber = c.getString(columns.indexOf(Utils.CallsColumns.FORMATTED_NUMBER));
                String senderId = Utils.firstTruthy(normalizedNumber, cachedFormattedNumber, number);

                int type = c.getInt(columns.indexOf(CallLog.Calls.TYPE));
                int missedCallsNo = type == CallLog.Calls.MISSED_TYPE ? 1 : 0;
                long duration = c.getLong(columns.indexOf(CallLog.Calls.DURATION));

                if (type == CallLog.Calls.OUTGOING_TYPE && duration == 0) {
                    continue;
                }

                if (items.size() > 0) {
                    Item prev = items.get(items.size() - 1);

                    if (prev.senderId.equals(normalizedNumber) || prev.senderId.equals(cachedFormattedNumber) || senderId.equals(number)) {
                        missedCallsNo = prev.extra.getInt(Item.Extra.MISSED_CALLS_NO);
                        duration += prev.extra.getInt(Item.Extra.CALL_DURATION);

                        if (type == CallLog.Calls.MISSED_TYPE && type == prev.extra.getInt(Item.Extra.CALL_TYPE)) {
                            missedCallsNo++;
                        }

                        items.remove(prev);
                    }
                }

                if (duration > 0) {
                    body = "You talked " + Utils.getHMS(duration);
                } else if (type == CallLog.Calls.MISSED_TYPE) {
                    body = "Missed " + missedCallsNo + (missedCallsNo == 1 ? " call" : " calls");
                } else {
                    continue;
                }

                JSONObject extra = new JSONObject();
                extra.put(Item.Extra.NUMBER, number);
                extra.put(Item.Extra.CALL_TYPE, type);
                extra.put(Item.Extra.CALL_DURATION, duration);
                extra.put(Item.Extra.MISSED_CALLS_NO, missedCallsNo);

                ContactData contactData = getContactData(number);

                if (contactData == null) {
                    contactData = getContactData(PhoneNumberUtils.formatNumber(senderId));
                }

                if (contactData != null) {
                    title = contactData.displayName;
                    String extraTitle = "";

                    if (getContactsWithMultipleNumbers().contains(contactData.displayName)) {
                        extraTitle = " " + number;
                    }

                    extra.put(Item.Extra.PHOTO_URI, contactData.photoUri);
                    extra.put(Item.Extra.FULL_TITLE, title + extraTitle);
                } else {
                    if (hideUnknown) {
                        continue;
                    }

                    title = Utils.firstTruthy(cachedFormattedNumber, number);
                    extra.put(Item.Extra.FULL_TITLE, title);
                }

                extra.put(Item.Extra.SYSTEM_MSG, "body");

                items.add(new Item(
                        c.getString(columns.indexOf(CallLog.Calls._ID)),
                        senderId,
                        new Date(c.getLong(columns.indexOf(CallLog.Calls.DATE))),
                        Item.Type.CALL,
                        title,
                        body,
                        extra
                ));
            } catch (JSONException e) {
                Log.e(e, e.getMessage());
            }
        } while (c.moveToNext());

        c.close();
        return items;
    }

    /**
     * Get new messages from the sms/mms content provider
     */
    public ArrayList<Item> getMessages() {
        ArrayList<Item> items = new ArrayList<Item>();
        boolean hideUnknown = prefs.getBoolean(Settings.KEY_HIDE_UNKNOWN, true);
        dbHelper.deleteData(Item.Type.SMS);

        ArrayList<String> columns = new ArrayList<String>();
        columns.add(Utils.SMSColumns._ID);
        columns.add(Utils.SMSColumns.DATE);
        columns.add(Utils.SMSColumns.ADDRESS);
        columns.add(Utils.SMSColumns.BODY);

        Cursor c = contentResolver.query(Uri.parse(Utils.CallsColumns.CONTENT_URI), columns.toArray(new String[columns.size()]), null, null, Utils.SMSColumns._ID + " DESC");

        if (c == null) {
            return items;
        } else if (!c.moveToFirst()) {
            c.close();
            return items;
        }

        do {
            try {
                String address = c.getString(columns.indexOf(Utils.SMSColumns.ADDRESS));

                String title;
                String body = c.getString(columns.indexOf(Utils.SMSColumns.BODY));
                String number = PhoneNumberUtils.formatNumber(address).replaceAll("-", " ");

                if (items.size() > 0 && (items.get(items.size() - 1).senderId.equals(address) || items.get(items.size() - 1).senderId.equals(number.replaceAll(" ", "")))) {
                    continue;
                }

                ContactData contactData = getContactData(PhoneNumberUtils.formatNumber(number));

                if (contactData == null && number.indexOf("+00") == 0) {
                    number = "+" + number.substring(3);
                    contactData = getContactData((PhoneNumberUtils.formatNumber(number)));
                }

                JSONObject extra = new JSONObject();
                extra.put(Item.Extra.NUMBER, address);

                if (contactData != null) {
                    title = contactData.displayName;
                    String extraTitle = "";

                    if (getContactsWithMultipleNumbers().contains(contactData.displayName)) {
                        extraTitle = " " + number;
                    }

                    extra.put(Item.Extra.FULL_TITLE, contactData.displayName + extraTitle);
                    extra.put(Item.Extra.PHOTO_URI, contactData.photoUri);
                } else {
                    if (hideUnknown) {
                        continue;
                    }

                    title = number;

                    extra.put(Item.Extra.FULL_TITLE, number);
                    extra.put(Item.Extra.PHOTO_URI, Uri.EMPTY);
                }

                if (TextUtils.isEmpty(body)) {
                    body = "Empty message";
                    extra.put(Item.Extra.SYSTEM_MSG, "body");
                }

                items.add(new Item(
                        c.getString(columns.indexOf(Utils.SMSColumns._ID)),
                        address,
                        new Date(c.getLong(columns.indexOf(Utils.SMSColumns.DATE))),
                        Item.Type.SMS,
                        title,
                        body,
                        extra
                ));
            } catch (JSONException e) {
                Log.e(e, e.getMessage());
            }
        } while (c.moveToNext());

        c.close();
        return items;
    }

    /**
     * Get new twitter mentions using the jtwitter lib
     */
    /*private static ArrayList<Item> getTwitterMentions(BigInteger lastId, SharedPreferences prefs) {
        ArrayList<Item> items = new ArrayList<Item>();
        List<Status> mentions;

        Twitter twitter = new Twitter("DroidHub", new OAuthSignpostClient(Settings.TWITTER_OAUTH_KEY, Settings.TWITTER_OAUTH_SECRET,
                prefs.getString(Settings.TWITTER_PREF_TOKEN, null), prefs.getString(Settings.TWITTER_PREF_SECRET, null)));

        twitter.setMaxResults(100);

        if (lastId.compareTo(BigInteger.ZERO) > 0) {
            twitter.setSinceId(lastId);
        }

        try {
            mentions = twitter.getMentions();
        } catch (TwitterException e) {
            Log.e(e, e.getMessage());
            return items;
        }

        for (Status status : mentions) {
            if (items.size() > 0 && items.get(items.size() - 1).senderId.equals(String.valueOf(status.getUser().getId()))) {
                continue;
            }

            try {
                JSONObject json = new JSONObject();
                json.put("photoUri", status.getUser().getProfileImageUrl());
                json.put("screenName", status.getUser().getScreenName());

                items.add(new Item(
                        String.valueOf(status.getId()),
                        String.valueOf(status.getUser().getId()),
                        status.getCreatedAt(),
                        Item.Type.TW_MN,
                        status.getUser().getName(),
                        status.getDisplayText(),
                        json
                ));
            } catch (JSONException e) {
                Log.e(e, e.getMessage());
            }
        }

        return items;
    }*/

    /**
     * Get new twitter direct messages using the jtwitter lib
     */
    /*private static ArrayList<Item> getTwitterMessages(BigInteger lastId, SharedPreferences prefs) {
        ArrayList<Item> items = new ArrayList<Item>();
        List<Message> messages;
        Twitter twitter = new Twitter("DroidHub", new OAuthSignpostClient(Settings.TWITTER_OAUTH_KEY, Settings.TWITTER_OAUTH_SECRET,
                prefs.getString(Settings.TWITTER_PREF_TOKEN, null), prefs.getString(Settings.TWITTER_PREF_SECRET, null)));

        twitter.setMaxResults(100);
        if (lastId.compareTo(BigInteger.ZERO) > 0)
            twitter.setSinceId(lastId);

        try {
            messages = twitter.getDirectMessages();
        } catch (TwitterException e) {
            Log.e(e, e.getMessage());
            return items;
        }

        for (winterwell.jtwitter.Message message : messages) {
            if (items.size() > 0 && items.get(items.size() - 1).senderId.equals(String.valueOf(message.getUser().getId()))) {
                continue;
            }

            try {
                JSONObject json = new JSONObject();
                json.put("photoUri", message.getUser().getProfileImageUrl());
                json.put("screenName", message.getUser().getScreenName());

                items.add(new Item(
                        String.valueOf(message.getId()),
                        String.valueOf(message.getUser().getId()),
                        message.getCreatedAt(),
                        Item.Type.TW_DM,
                        message.getUser().getName(),
                        message.getDisplayText(),
                        json
                ));
            } catch (JSONException e) {
                Log.e(e, e.getMessage());
            }
        }

        return items;
    }*/

    /**
     * Object holder for contact data (number, photo_uri, display_name)
     */
    private static class ContactData {
        public final String number;
        public final String photoUri;
        public final String displayName;

        private ContactData(String number, String photoUri, String displayName) {
            this.number = number;
            this.photoUri = photoUri;
            this.displayName = displayName;
        }
    }

    /**
     * Tests if the app can retrieve call logs
     */
    public boolean canGetCalls() {
        Cursor c = contentResolver.query(CallLog.Calls.CONTENT_URI, null, null, null, null);
        return c != null && c.moveToFirst();
    }

    /**
     * Tests if the app can retrieve sms messages
     */
    public boolean canGetMessages() {
        Cursor c = contentResolver.query(Uri.parse(Utils.CallsColumns.CONTENT_URI), null, null, null, null);
        return c != null && c.moveToFirst();
    }
}