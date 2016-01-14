package com.aluxian.apps.droidhub.adapters;

import android.content.Context;
import android.os.AsyncTask;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.TextView;
import com.aluxian.apps.droidhub.DBHelper;
import com.aluxian.apps.droidhub.R;
import com.aluxian.apps.droidhub.models.Account;
import com.aluxian.apps.droidhub.models.Item;

import java.util.ArrayList;

/**
 * Adapter used in accounts listView
 */
public class AccountsAdapter extends BaseAdapter {
    private Context context;
    private ArrayList<Account> accounts;

    public AccountsAdapter(Context context, ArrayList<Account> accounts) {
        this.context = context;
        this.accounts = accounts;
    }

    @Override
    public View getView(int position, View view, ViewGroup parent) {
        Account account = getItem(position);
        ViewHolder holder = new ViewHolder();

        if (view == null) {
            view = LayoutInflater.from(context).inflate(R.layout.account_item, null);
            holder.title = (TextView) view.findViewById(R.id.title);
            view.setTag(holder);
        } else {
            holder = (ViewHolder) view.getTag();
        }

        holder.title.setText(account.type.accName);
        return view;
    }

    @Override
    public int getCount() {
        return accounts.size();
    }

    @Override
    public Account getItem(int position) {
        return accounts.get(position);
    }

    @Override
    public long getItemId(int position) {
        return position;
    }

    private static class ViewHolder {
        TextView title;
    }
}