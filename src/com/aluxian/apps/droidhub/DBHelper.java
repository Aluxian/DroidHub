package com.aluxian.apps.droidhub;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.SQLException;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteException;
import android.database.sqlite.SQLiteOpenHelper;
import android.preference.PreferenceManager;
import com.aluxian.apps.droidhub.models.Account;
import com.aluxian.apps.droidhub.models.Item;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.Date;

/**
 * DB Interaction Helper
 */
public class DBHelper extends SQLiteOpenHelper {
    /**
     * Database info
     */
    public static final int DB_VERSION = 2;
    public static final String DB_NAME = "main_db";

    /**
     * Table names
     */
    public static final String TABLE_ITEMS = "items";
    public static final String TABLE_ACCOUNTS = "accounts";

    /**
     * Items and Accounts columns
     */
    public static final String COL_ROW_ID = "_id";
    public static final String COL_ITEM_ID = "item_id";
    public static final String COL_SENDER_ID = "sender_id";
    public static final String COL_DATE = "date";
    public static final String COL_TYPE = "type";
    public static final String COL_TITLE = "title";
    public static final String COL_BODY = "body";
    public static final String COL_EXTRA = "extra";
    public static final String COL_CREDENTIALS = "credentials";

    private Context context;

    public DBHelper(Context context) {
        super(context, DB_NAME, null, DB_VERSION);
        this.context = context;
    }

    /**
     * Add items to the db
     */
    public void addItems(ArrayList<Item> items) {
        SQLiteDatabase db = getWritableDatabase();

        /* Sort items from oldest to newest */
        Collections.sort(items, new Comparator<Item>() {
            @Override
            public int compare(Item lhs, Item rhs) {
                long lt = lhs.date.getTime();
                long rt = rhs.date.getTime();

                if (lt < rt) {
                    return -1;
                } else if (lt > rt) {
                    return 1;
                } else {
                    long lid = Long.valueOf(lhs.itemId);
                    long rid = Long.valueOf(rhs.itemId);

                    if (lid < rid) {
                        return -1;
                    } else if (lid > rid) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            }
        });

        db.beginTransaction();

        try {
            for (Item item : items) {
                ContentValues values = new ContentValues();

                values.put(DBHelper.COL_ITEM_ID, item.itemId);
                values.put(DBHelper.COL_SENDER_ID, item.senderId);
                values.put(DBHelper.COL_DATE, item.date.getTime());
                values.put(DBHelper.COL_TYPE, item.type.name());
                values.put(DBHelper.COL_TITLE, item.title);
                values.put(DBHelper.COL_BODY, item.body);
                values.put(DBHelper.COL_EXTRA, item.extra.toString());

                db.insert(DBHelper.TABLE_ITEMS, null, values);
            }

            db.setTransactionSuccessful();
        } catch (SQLiteException e) {
            Log.e(e, e.getMessage());
        }

        db.endTransaction();
        db.close();
    }

    /**
     * Get items from the db
     */
    public GetItemsResult getItems(String selection, String[] selectionArgs, long prevDate) {
        ArrayList<Item> items = new ArrayList<Item>();
        String lastDate = Item.formatHeaderDate.format(new Date(prevDate));

        SQLiteDatabase db = getReadableDatabase();
        String[] columns = new String[]{
                COL_ROW_ID,
                COL_ITEM_ID,
                COL_SENDER_ID,
                COL_DATE,
                COL_TYPE,
                COL_TITLE,
                COL_BODY,
                COL_EXTRA
        };

        Cursor c = db.query(TABLE_ITEMS, columns, selection, selectionArgs, null, null, COL_DATE + " DESC, " + COL_ROW_ID + " DESC", "50");

        if (!c.moveToFirst()) {
            c.close();
            return new GetItemsResult(items, false);
        }

        do {
            try {
                Item item = new Item(
                        c.getString(c.getColumnIndex(COL_ROW_ID)),
                        c.getString(c.getColumnIndex(COL_ITEM_ID)),
                        c.getString(c.getColumnIndex(COL_SENDER_ID)),
                        new Date(c.getLong(c.getColumnIndex(COL_DATE))),
                        Item.Type.valueOf(c.getString(c.getColumnIndex(COL_TYPE))),
                        c.getString(c.getColumnIndex(COL_TITLE)),
                        c.getString(c.getColumnIndex(COL_BODY)),
                        new JSONObject(c.getString(c.getColumnIndex(COL_EXTRA)))
                );

                String itemDate = Item.formatHeaderDate.format(item.date);

                if (!lastDate.equals(itemDate)) {
                    lastDate = itemDate;
                    items.add(new Item(item.date));
                }

                items.add(item);
            } catch (JSONException e) {
                Log.e(e, e.getMessage());
            }
        } while (c.moveToNext());

        int cSize = c.getCount();

        db.close();
        c.close();

        return new GetItemsResult(items, cSize == 50);
    }

    /**
     * Insert one account into db
     */
    public String addAccount(Account account) {
        SQLiteDatabase db = getWritableDatabase();

        try {
            ContentValues values = new ContentValues();

            values.put(DBHelper.COL_TYPE, account.type.name());
            values.put(DBHelper.COL_CREDENTIALS, account.credentials != null ? account.credentials.toString() : null);

            db.insert(DBHelper.TABLE_ACCOUNTS, null, values);
        } catch (SQLiteException e) {
            Log.e(e, e.getMessage());
        }

        db.close();
        return null;
    }

    /**
     * Remove one account from db
     */
    public void removeAccount(String id) {
        SQLiteDatabase db = getWritableDatabase();
        db.delete(TABLE_ACCOUNTS, COL_ROW_ID + " = ?", new String[]{id});
        db.close();
    }

    /**
     * Get user's accounts
     */
    public ArrayList<Account> getAccounts() {
        ArrayList<Account> accounts = new ArrayList<Account>();

        SQLiteDatabase db = getReadableDatabase();
        Cursor c = db.query(TABLE_ACCOUNTS, null, null, null, null, null, null);

        if (!c.moveToFirst()) {
            c.close();
            return accounts;
        }

        do {
            try {
                String credentials = c.getString(2);

                accounts.add(new Account(
                        c.getString(0),
                        Item.Type.valueOf(c.getString(1)),
                        credentials != null ? new JSONObject(credentials) : new JSONObject()
                ));
            } catch (JSONException e) {
                Log.e(e, e.getMessage());
            }
        } while (c.moveToNext());

        db.close();
        c.close();

        return accounts;
    }

    /**
     * Delete items from the db
     */
    public boolean deleteData(Item.Type type) {
        try {
            SQLiteDatabase db = getWritableDatabase();

            if (type == null) {
                db.delete(TABLE_ITEMS, null, null);
            } else {
                db.delete(TABLE_ITEMS, COL_TYPE + " = ?", new String[]{type.name()});
            }

            db.close();
            return true;
        } catch (SQLException e) {
            Log.e(e, e.getMessage());
            return false;
        }
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        db.execSQL("CREATE TABLE " + TABLE_ITEMS + " ("
                + COL_ROW_ID + " INTEGER PRIMARY KEY AUTOINCREMENT, "
                + COL_ITEM_ID + " TEXT, "
                + COL_SENDER_ID + " TEXT, "
                + COL_DATE + " INTEGER, "
                + COL_TYPE + " TEXT, "
                + COL_TITLE + " TEXT, "
                + COL_BODY + " TEXT, "
                + COL_EXTRA + " TEXT);"
        );

        db.execSQL("CREATE TABLE " + TABLE_ACCOUNTS + " ("
                + COL_ROW_ID + " INTEGER PRIMARY KEY AUTOINCREMENT, "
                + COL_TYPE + " TEXT, "
                + COL_CREDENTIALS + " TEXT);"
        );
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        Log.v("db upgrade from " + oldVersion + " to " + newVersion);

        PreferenceManager.getDefaultSharedPreferences(context).edit().clear().apply();
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_ITEMS + ";");
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_ACCOUNTS + ";");
        onCreate(db);
    }

    /**
     * Object returned by getItems()
     */
    public class GetItemsResult {
        public final ArrayList<Item> items;
        public final boolean hasMore;

        public GetItemsResult(ArrayList<Item> items, boolean hasMore) {
            this.items = items;
            this.hasMore = hasMore;
        }
    }
}