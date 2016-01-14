package com.aluxian.apps.droidhub.adapters;

import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;

import java.util.ArrayList;

/**
 * Adapter for the main ViewPager
 */
public class PagerAdapter extends FragmentPagerAdapter {
    private ArrayList<Fragment> fragments = new ArrayList<Fragment>();

    public PagerAdapter(FragmentManager fm) {
        super(fm);
    }

    @Override
    public Fragment getItem(int position) {
        return fragments.get(position);
    }

    @Override
    public int getCount() {
        return fragments.size();
    }

    @Override
    public int getItemPosition(Object object) {
        int index = fragments.indexOf(object);

        if (index == -1){
            return POSITION_NONE;
        }

        return index;
    }

    public void addFragment(Fragment fragment) {
        fragments.add(fragment);
        notifyDataSetChanged();
    }
}