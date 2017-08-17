package com.aluxian.apps.droidhub.adapters;

import android.view.View;
import android.view.ViewGroup;
import android.view.animation.AnimationUtils;
import com.aluxian.apps.droidhub.DBHelper;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.R;
import com.aluxian.apps.droidhub.Refresh;
import com.aluxian.apps.droidhub.activities.MainActivity;
import com.aluxian.apps.droidhub.models.Item;
import com.aluxian.apps.droidhub.utilitaries.SelectionQueryBuilder;
import com.commonsware.cwac.endless.EndlessAdapter;

import java.util.Calendar;
import java.util.Date;
import java.util.List;

/**
 * Adapter wrapped around ItemsAdapter to only display some items then load more when the user scrolls to the bottom
 */
public class LoaderAdapter extends EndlessAdapter {
    private MainActivity mainActivity;
    private ItemsAdapter itemsAdapter;

    private List<Item> items;
    private List<Item> tempList;

    private Item.Type type;
    private boolean getNew;
    private boolean isReloading;
    private View emptyText;

    public LoaderAdapter(MainActivity mainActivity, List<Item> items) {
        super(new ItemsAdapter(mainActivity, items));
        this.items = items;
        this.mainActivity = mainActivity;

        emptyText = mainActivity.findViewById(R.id.emptyText);
        itemsAdapter = (ItemsAdapter) getWrappedAdapter();
    }

    @Override
    protected View getPendingView(ViewGroup parent) {
        final View footer = mainActivity.getLayoutInflater().inflate(R.layout.list_footer, null);

        new Thread(new Runnable() {
            @Override
            public void run() {
                mainActivity.runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        footer.findViewById(R.id.refreshLeft).startAnimation(AnimationUtils.loadAnimation(mainActivity, R.anim.blink));
                    }
                });

                delay();

                mainActivity.runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        footer.findViewById(R.id.refreshMiddle).startAnimation(AnimationUtils.loadAnimation(mainActivity, R.anim.blink));
                    }
                });

                delay();

                mainActivity.runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        footer.findViewById(R.id.refreshRight).startAnimation(AnimationUtils.loadAnimation(mainActivity, R.anim.blink));
                    }
                });
            }

            private void delay() {
                try {
                    synchronized (this) {
                        wait(300);
                    }
                } catch (InterruptedException e) {
                    Log.e(e, e.getMessage());
                }
            }
        }).start();

        return footer;
    }

    /**
     * Reload items with the same type as before and fetch for new activity
     */
    public void reloadNew() {
        reload(type, true);
    }

    /**
     * Reload items matching type
     */
    public void reloadType(Item.Type type) {
        reload(type, false);
    }

    /**
     * Reload items and fetch for new activity matching type
     */
    public void reload(Item.Type type, boolean getNew) {
        stopAppending();

        if (type == Item.Type.ALL) {
            type = null;
        }

        this.isReloading = true;
        this.type = type;
        this.getNew = getNew;
        this.tempList = null;

        items.clear();
        itemsAdapter.notifyDataSetChanged();

        isReloading = false;
        restartAppending();
    }

    @Override
    protected boolean cacheInBackground() throws Exception {
        if (isReloading) {
            return false;
        }

        synchronized (this) {
            wait(5000);
        }

        Calendar cal = Calendar.getInstance();
        cal.setTime(items.size() > 0 ? items.get(items.size() - 1).date : new Date());
        DBHelper dbHelper = new DBHelper(mainActivity);

        if (getNew) {
            getNew = false;
            dbHelper.addItems(new Refresh(mainActivity).getAll(mainActivity.accounts));
        }

        SelectionQueryBuilder query = new SelectionQueryBuilder();

        if (type != null) {
            query.and(DBHelper.COL_TYPE, SelectionQueryBuilder.Op.EQ, type.name());
        }

        long prevDate = 0;

        if (items.size() > 0) {
            prevDate = items.get(items.size() - 1).date.getTime();
            query.and(DBHelper.COL_DATE, SelectionQueryBuilder.Op.LT, prevDate);
        }

        DBHelper.GetItemsResult result = dbHelper.getItems(query.getQuery(), query.getArgsArray(), prevDate);
        tempList = result.items;

        return result.hasMore;
    }

    @Override
    protected void appendCachedData() {
        if (isReloading) {
            return;
        }

        items.addAll(tempList);

        if (items.size() > 0 || tempList == null) {
            emptyText.setVisibility(View.GONE);
        } else {
            emptyText.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public void onDataReady() {
        super.onDataReady();

        /*if (!sliderLoaded && getWrappedAdapter().getCount() != 0) {
            MainFragment fragment = (MainFragment) activity.getFragmentManager().findFragmentByTag("MainFragment");
            fragment.loadSlider();
            sliderLoaded = true;
        }*/
    }
}