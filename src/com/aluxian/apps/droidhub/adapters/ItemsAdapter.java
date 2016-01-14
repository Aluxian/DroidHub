package com.aluxian.apps.droidhub.adapters;

import android.app.Activity;
import android.graphics.Typeface;
import android.graphics.drawable.ShapeDrawable;
import android.graphics.drawable.shapes.RoundRectShape;
import android.net.Uri;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.ImageView;
import android.widget.RelativeLayout;
import android.widget.TextView;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.R;
import com.aluxian.apps.droidhub.activities.MainActivity;
import com.aluxian.apps.droidhub.models.Item;
import com.squareup.picasso.Picasso;
import org.json.JSONException;

import java.util.List;

/**
 * Adapter used in the main list view which displays user activity
 */
@SuppressWarnings("deprecation")
public class ItemsAdapter extends BaseAdapter {

    private Activity activity;
    private List<Item> items;

    public ItemsAdapter(Activity activity, List<Item> items) {
        this.activity = activity;
        this.items = items;
    }

    @Override
    public View getView(int position, View view, ViewGroup parent) {
        Item item = getItem(position);
        ViewHolder holder = new ViewHolder();

        if (view == null) {
            if ((view = LayoutInflater.from(activity).inflate(activity.getResources().getIdentifier(item.type.layout, "layout", activity.getPackageName()), null)) != null) {
                holder.date = (TextView) view.findViewById(R.id.date);
                holder.title = (TextView) view.findViewById(R.id.title);
                holder.body = (TextView) view.findViewById(R.id.body);
                holder.icon = (ImageView) view.findViewById(R.id.icon);
                holder.surface = (RelativeLayout) view.findViewById(R.id.surface);

                view.setTag(holder);
            }
        } else {
            holder = (ViewHolder) view.getTag();
        }

        if (item.type == Item.Type.DATE) {
            holder.date.setText(item.headerDate);
        } else {
            ShapeDrawable background = new ShapeDrawable();
            background.setShape(new RoundRectShape(new float[]{1, 1, 1, 1, 0, 0, 0, 0}, null, null));
            background.getPaint().setColor(item.type.color);

            holder.icon.setImageResource(R.drawable.ic_contact_picture);
            if (item.extra.has(Item.Extra.PHOTO_URI)) {
                try {
                    String uri = item.extra.getString(Item.Extra.PHOTO_URI);

                    Picasso
                            .with(activity)
                            .load(Uri.parse(uri))
                            .placeholder(R.drawable.ic_contact_picture)
                            .error(R.drawable.ic_contact_picture)
                            .into(holder.icon);
                } catch (JSONException e) {
                    Log.e(e, e.getMessage());
                    holder.icon.setImageResource(R.drawable.ic_contact_picture);
                }
            }

            holder.surface.setBackgroundDrawable(background);
            holder.title.setText(item.title);
            holder.body.setText(item.body);

            if (item.extra.has(Item.Extra.SYSTEM_MSG)) {
                try {
                    String systemMsg = item.extra.getString(Item.Extra.SYSTEM_MSG);

                    if (systemMsg.contains("title")) {
                        holder.title.setTypeface(null, Typeface.ITALIC);
                    } else {
                        holder.title.setTypeface(null, Typeface.NORMAL);
                    }

                    if (systemMsg.contains("body")) {
                        holder.body.setTypeface(null, Typeface.ITALIC);
                    } else {
                        holder.body.setTypeface(null, Typeface.NORMAL);
                    }
                } catch (JSONException e) {
                    Log.e(e, e.getMessage());
                }
            } else {
                holder.title.setTypeface(null, Typeface.NORMAL);
                holder.body.setTypeface(null, Typeface.NORMAL);
            }
        }

        return view;
    }

    @Override
    public int getCount() {
        return items.size();
    }

    @Override
    public Item getItem(int position) {
        return items.get(position);
    }

    @Override
    public long getItemId(int position) {
        return position;
    }

    @Override
    public int getViewTypeCount() {
        return Item.Type.values().length;
    }

    @Override
    public int getItemViewType(int position) {
        return getItem(position).type.id;
    }

    @Override
    public boolean isEnabled(int position) {
        return !((MainActivity) activity).isSliderOpen && getItem(position).type.id != Item.Type.DATE.id;

    }

    private static class ViewHolder {
        TextView date;
        TextView title;
        TextView body;
        ImageView icon;
        RelativeLayout surface;
    }
}