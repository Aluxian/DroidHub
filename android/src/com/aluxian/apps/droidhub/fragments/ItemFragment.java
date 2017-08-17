package com.aluxian.apps.droidhub.fragments;

import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.widget.DrawerLayout;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import com.aluxian.apps.droidhub.R;
import com.aluxian.apps.droidhub.activities.MainActivity;
import com.aluxian.apps.droidhub.models.Item;

/**
 * Login fragment for user login
 */
public class ItemFragment extends Fragment {
    private MainActivity mainActivity;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        mainActivity = (MainActivity) getActivity();
        return inflater.inflate(R.layout.fragment_main, container, false);
    }

    public void loadItem(Item item) {

    }
}