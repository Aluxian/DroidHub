package com.aluxian.apps.droidhub.activities;

import android.app.ActionBar;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.content.res.Configuration;
import android.database.sqlite.SQLiteDatabase;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.StrictMode;
import android.preference.PreferenceManager;
import android.support.v4.app.ActionBarDrawerToggle;
import android.support.v4.app.FragmentActivity;
import android.support.v4.view.ViewPager;
import android.support.v4.widget.DrawerLayout;
import android.view.*;
import android.widget.AdapterView;
import android.widget.ListView;
import com.aluxian.apps.droidhub.DBHelper;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.R;
import com.aluxian.apps.droidhub.Refresh;
import com.aluxian.apps.droidhub.adapters.AccountsAdapter;
import com.aluxian.apps.droidhub.adapters.PagerAdapter;
import com.aluxian.apps.droidhub.fragments.ItemFragment;
import com.aluxian.apps.droidhub.fragments.MainFragment;
import com.aluxian.apps.droidhub.models.Account;
import com.aluxian.apps.droidhub.models.Item;
import com.aluxian.apps.droidhub.utilitaries.Keys;
import com.aluxian.apps.droidhub.utilitaries.Utils;
import com.crittercism.app.Crittercism;
import com.google.analytics.tracking.android.EasyTracker;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;

/**
 * The main activity the app runs
 */
public class MainActivity extends FragmentActivity {
    private SharedPreferences sharedPreferences;

    private ListView accountsList;
    private AccountsAdapter accountsAdapter;

    private ActionBarDrawerToggle drawerAbToggle;
    private DrawerLayout drawerLayout;

    public boolean isSliderOpen;
    public ArrayList<Account> accounts = new ArrayList<Account>();

    public ViewPager viewPager;
    public PagerAdapter pagerAdapter;

    public MainFragment mainFragment;
    public ItemFragment itemFragment;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        if (Log.STRICT_MODE) {
            StrictMode.enableDefaults();
        }

        if (Log.CRITTERCISM) {
            Crittercism.initialize(getApplicationContext(), "522a584297c8f23137000001");
        }

        sharedPreferences = PreferenceManager.getDefaultSharedPreferences(getApplicationContext());

        try {
            int versionCode = getPackageManager().getPackageInfo(getPackageName(), 0).versionCode;
            int lastVersionCode = sharedPreferences.getInt(Keys.PREF_LAST_UPDATED_VERSION, 0);

            if (versionCode >= lastVersionCode && lastVersionCode > 0) {
                SQLiteDatabase db = new DBHelper(getApplicationContext()).getReadableDatabase();

                if (db != null) {
                    db.close();
                    sharedPreferences.edit().putInt(Keys.PREF_LAST_UPDATED_VERSION, versionCode).apply();
                }
            }
        } catch (PackageManager.NameNotFoundException e) {
            Log.e(e, e.getMessage());
        }

        pagerAdapter = new PagerAdapter(getSupportFragmentManager());
        pagerAdapter.addFragment(mainFragment = new MainFragment());

        viewPager = (ViewPager) findViewById(R.id.viewPager);
        viewPager.setAdapter(pagerAdapter);

        viewPager.setOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int i, float v, int i2) {

            }

            @Override
            public void onPageSelected(int i) {
                if (i == 0) {
                    drawerLayout.setDrawerLockMode(DrawerLayout.LOCK_MODE_UNLOCKED);
                } else {
                    drawerLayout.setDrawerLockMode(DrawerLayout.LOCK_MODE_LOCKED_CLOSED);
                }
            }

            @Override
            public void onPageScrollStateChanged(int i) {

            }
        });

        ActionBar actionBar = getActionBar();
        actionBar.setIcon(null);
        actionBar.setDisplayOptions(ActionBar.DISPLAY_SHOW_TITLE | ActionBar.DISPLAY_SHOW_HOME | ActionBar.DISPLAY_HOME_AS_UP);

        drawerLayout = (DrawerLayout) findViewById(R.id.drawerLayout);
        drawerAbToggle = new ActionBarDrawerToggle(this, drawerLayout, R.drawable.ic_drawer, R.string.drawer_open, R.string.drawer_close) {
            float prevOffset;

            @Override
            public void onDrawerSlide(View drawerView, float slideOffset) {
                super.onDrawerSlide(drawerView, slideOffset);

                isSliderOpen = prevOffset <= slideOffset;
                prevOffset = slideOffset;

                invalidateOptionsMenu();
            }
        };
        drawerLayout.setDrawerListener(drawerAbToggle);

        accountsList = (ListView) findViewById(R.id.drawerList);
        accountsAdapter = new AccountsAdapter(getApplicationContext(), accounts);
        accountsList.setAdapter(accountsAdapter);

        accountsList.setOnItemLongClickListener(new AdapterView.OnItemLongClickListener() {
            @Override
            public boolean onItemLongClick(AdapterView<?> parent, View view, final int position, long id) {
                if (position == 0) {
                    return false;
                }

                Account account = accountsAdapter.getItem(position);
                AlertDialog.Builder builder = new AlertDialog.Builder(new ContextThemeWrapper(MainActivity.this, R.style.DroidHub_AlertDialog));

                builder.setTitle("Delete account");
                builder.setMessage("Do you really want to remove " + account.type.accName + "?");

                builder.setPositiveButton("Yes", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        removeAccount(position);
                    }
                });

                builder.setNegativeButton("No", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {

                    }
                });

                builder.show();
                return true;
            }
        });

        accountsList.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                mainFragment.adapter.reloadType(accountsAdapter.getItem(position).type);
                drawerLayout.closeDrawer(accountsList);
            }
        });

        new BackgroundTasks().execute();
    }

    private class BackgroundTasks extends AsyncTask<Void, Void, Void> {
        private ArrayList<Account> newAccounts = new ArrayList<Account>();

        @Override
        protected Void doInBackground(Void... params) {
            newAccounts.add(new Account(Item.Type.ALL, null));
            newAccounts.addAll(new DBHelper(getApplicationContext()).getAccounts());

            if (sharedPreferences.getBoolean(Settings.KEY_REFRESH, true)) {
                Utils.setBackgroundRefresh(getApplicationContext());
            }

            return null;
        }

        @Override
        protected void onPostExecute(Void aVoid) {
            accounts.addAll(newAccounts);
            sortAccounts();
            accountsAdapter.notifyDataSetChanged();
        }
    }

    @Override
    protected void onStart() {
        super.onStart();

        if (Log.GANALYTICS) {
            EasyTracker.getInstance(getApplicationContext()).activityStart(this);
        }
    }

    @Override
    protected void onStop() {
        super.onStop();

        if (Log.GANALYTICS) {
            EasyTracker.getInstance(getApplicationContext()).activityStop(this);
        }
    }

    @Override
    protected void onPostCreate(Bundle savedInstanceState) {
        super.onPostCreate(savedInstanceState);
        drawerAbToggle.syncState();
    }

    @Override
    public void onConfigurationChanged(Configuration newConfig) {
        super.onConfigurationChanged(newConfig);
        drawerAbToggle.onConfigurationChanged(newConfig);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater inflater = getMenuInflater();

        if (isSliderOpen) {
            inflater.inflate(R.menu.accounts, menu);
        } else {
            inflater.inflate(R.menu.main, menu);
        }

        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case android.R.id.home:
                drawerLayout.closeDrawer(Gravity.START);
                break;

            case R.id.menu_refresh:
                mainFragment.adapter.reloadNew();
                break;

            case R.id.menu_settings:
                startActivity(new Intent(getApplicationContext(), Settings.class));
                break;

            case R.id.menu_add_call: {
                if (accountAlreadyAdded(Item.Type.CALL)) {
                    showDialog("Cannot add", "You've already added a Call Logs account.");
                } else if (new Refresh(getApplicationContext()).canGetCalls()) {
                    addAccount(new Account(Item.Type.CALL, null));
                } else {
                    showDialog("Cannot add", "Call Logs couldn't be retrieved from your device.");
                }

                break;
            }

            case R.id.menu_add_sms: {
                if (accountAlreadyAdded(Item.Type.SMS)) {
                    showDialog("Cannot add", "You've already added an SMS Messages account.");
                } else if (new Refresh(getApplicationContext()).canGetCalls()) {
                    addAccount(new Account(Item.Type.SMS, null));
                } else {
                    showDialog("Cannot add", "SMS Messages couldn't be retrieved from your device.");
                }

                break;
            }

            case R.id.menu_add_tw_mn:

                break;

            case R.id.menu_add_tw_dm:

                break;

            case R.id.menu_add_gmail:

                break;
        }

        return drawerAbToggle.onOptionsItemSelected(item) || super.onOptionsItemSelected(item);
    }

    @Override
    public void onBackPressed() {
        if (viewPager.getCurrentItem() == 0) {
            super.onBackPressed();
        } else {
            viewPager.setCurrentItem(viewPager.getCurrentItem() - 1);
        }
    }

    /**
     * Add an account
     */
    private void addAccount(final Account account) {
        new AsyncTask<Void, Void, Void>() {
            @Override
            protected Void doInBackground(Void... params) {
                account.id = new DBHelper(getApplicationContext()).addAccount(account);
                return null;
            }

            @Override
            protected void onPostExecute(Void aVoid) {
                accounts.add(account);
                sortAccounts();
                accountsAdapter.notifyDataSetChanged();
            }
        }.execute();
    }

    /**
     * Remove an account
     */
    private void removeAccount(int position) {
        final Account account = accountsAdapter.getItem(position);

        accounts.remove(position);
        accountsAdapter.notifyDataSetChanged();

        new Thread(new Runnable() {
            @Override
            public void run() {
                new DBHelper(getApplicationContext()).removeAccount(account.id);
            }
        }).start();
    }

    /**
     * Order accounts alphabetically
     * TODO: Make sure All stays at the top
     * */
    public void sortAccounts() {
        Collections.sort(accounts, new Comparator<Account>() {
            @Override
            public int compare(Account lAcc, Account rAcc) {
                return lAcc.type.name().compareTo(rAcc.type.name());
            }
        });
    }

    /**
     * Check if type account has already been added
     */
    private boolean accountAlreadyAdded(Item.Type type) {
        for (Account account : accounts) {
            if (account.type == type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show a simple dialog
     */
    private void showDialog(String title, String message) {
        AlertDialog.Builder builder = new AlertDialog.Builder(new ContextThemeWrapper(getApplicationContext(), R.style.DroidHub_AlertDialog));
        builder.setTitle(title);
        builder.setMessage(message);
        builder.setNeutralButton("OK", null);
        builder.show();
    }
}
