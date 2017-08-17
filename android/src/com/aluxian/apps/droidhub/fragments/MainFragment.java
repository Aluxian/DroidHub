package com.aluxian.apps.droidhub.fragments;

import android.app.*;
import android.content.*;
import android.graphics.Color;
import android.graphics.drawable.ShapeDrawable;
import android.graphics.drawable.shapes.RoundRectShape;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.support.v4.app.DialogFragment;
import android.support.v4.app.ListFragment;
import android.support.v4.widget.DrawerLayout;
import android.telephony.SmsManager;
import android.view.*;
import android.widget.*;
import com.aluxian.apps.droidhub.Log;
import com.aluxian.apps.droidhub.R;
import com.aluxian.apps.droidhub.utilitaries.Utils;
import com.aluxian.apps.droidhub.activities.MainActivity;
import com.aluxian.apps.droidhub.adapters.LoaderAdapter;
import com.aluxian.apps.droidhub.models.Item;
import org.json.JSONException;

import java.util.ArrayList;

/**
 * Main fragment which displays user activity
 */
public class MainFragment extends ListFragment {
    private MainActivity mainActivity;
    private SharedPreferences prefs;

    public LoaderAdapter adapter;
    public ListView listView;

    @Override
    public void onActivityCreated(Bundle saveState) {
        super.onActivityCreated(saveState);
        prefs = PreferenceManager.getDefaultSharedPreferences(mainActivity);

        listView = getListView();
        listView.addHeaderView(mainActivity.getLayoutInflater().inflate(R.layout.list_header, null, false), null, false);

        adapter = new LoaderAdapter(mainActivity, new ArrayList<Item>());
        listView.setAdapter(adapter);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        mainActivity = (MainActivity) getActivity();
        return inflater.inflate(R.layout.fragment_main, container, false);
    }

    @Override
    public void onListItemClick(ListView l, View v, int position, long id) {
        if (position <= 1) {
            return;
        }

        if (mainActivity.itemFragment == null) {
            mainActivity.pagerAdapter.addFragment(mainActivity.itemFragment = new ItemFragment());
        }

        mainActivity.viewPager.setCurrentItem(1);
        mainActivity.itemFragment.loadItem((Item) adapter.getItem(position - 1));
    }

    /**
     * Dialog to send an sms
     */
    private static class SendSMSDialog extends DialogFragment {
        private String number;
        private String message;
        private String contact;

        private SendSMSDialog(String number, String message, String contact) {
            this.number = number;
            this.message = message;
            this.contact = contact;
        }

        @Override
        public Dialog onCreateDialog(Bundle savedInstanceState) {
            final View view = null;//getActivity().getLayoutInflater().inflate(R.layout.dialog_send_sms, null);
            final EditText messageText = (EditText) view.findViewById(R.id.viewPager);
            messageText.setText(message);
            messageText.setSelection(messageText.getText().length());

            return new AlertDialog.Builder(getActivity()) // new ContextThemeWrapper(getActivity(), R.style.DroidHub_Light_Dialog))
                    .setTitle(contact)
                    .setView(view)
                    .setPositiveButton("Send", new DialogInterface.OnClickListener() {
                        public void onClick(DialogInterface dialog, int id) {
                            message = messageText.getText().toString();

                            if (message.isEmpty()) {
                                Toast.makeText(getActivity(), "Empty message", Toast.LENGTH_SHORT).show();

                                DialogFragment newFragment = new SendSMSDialog(number, message, contact);
                                newFragment.show(getFragmentManager(), "SendSMSDialog");

                                return;
                            }

                            final ProgressDialog sendStatus = new ProgressDialog(getActivity(), ProgressDialog.THEME_HOLO_LIGHT);
                            sendStatus.setMessage("Sending...");
                            sendStatus.show();

                            TextView tv = (TextView) sendStatus.findViewById(android.R.id.message);
                            tv.setTextColor(Color.parseColor("#33b5e5"));
                            tv.setTextSize(20);

                            new AsyncTask<Void, Void, String>() {
                                private String smsMessage = "";
                                private int smsPos = 0;

                                @Override
                                protected String doInBackground(Void... params) {
                                    Context context = getActivity();

                                    SmsManager smsManager = SmsManager.getDefault();
                                    final ArrayList<String> parts = smsManager.divideMessage(message);
                                    ArrayList<PendingIntent> sentIntents = new ArrayList<PendingIntent>();

                                    for (String part : parts) {
                                        sentIntents.add(PendingIntent.getBroadcast(context, 0, new Intent("B_SMS_SENT"), 0));
                                    }

                                    sendStatus.setMessage("Sending SMS 1/" + parts.size() + "...");
                                    context.registerReceiver(new BroadcastReceiver() {
                                        @Override
                                        public void onReceive(Context context, Intent intent) {
                                            String body = parts.get(smsPos);
                                            String messageIndex = (smsPos + 1) + "/" + parts.size();

                                            switch (getResultCode()) {
                                                case Activity.RESULT_OK:
                                                    smsMessage += body;
                                                    Log.v("Send SMS " + messageIndex + " to " + contact + " success");

                                                    break;
                                                default:
                                                    smsMessage += "*part not sent*";
                                                    Log.v("Send SMS " + messageIndex + " to " + contact + " failed = " + getResultCode());
                                                    Toast.makeText(context, "Couldn't send SMS " + messageIndex + " to " + contact, Toast.LENGTH_SHORT).show();
                                            }

                                            smsPos++;

                                            if (smsPos == parts.size()) {
                                                sendStatus.dismiss();

                                                ContentValues values = new ContentValues();
                                                values.put(Utils.SMSColumns.ADDRESS, number);
                                                values.put(Utils.SMSColumns.BODY, smsMessage);
                                                context.getContentResolver().insert(Uri.parse("content://sms/sent"), values);

                                                Toast.makeText(context, "SMS sent to " + contact, Toast.LENGTH_SHORT).show();
                                            } else {
                                                sendStatus.setMessage("Sending SMS " + (smsPos + 1) + "/" + parts.size() + "...");
                                            }

                                            Log.v("sms sent received");
                                        }
                                    }, new IntentFilter("B_SMS_SENT"));

                                    smsManager.sendMultipartTextMessage(number, null, parts, sentIntents, null);
                                    return null;
                                }
                            }.execute();
                        }
                    })
                    .setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
                        public void onClick(DialogInterface dialog, int id) {
                        }
                    })
                    .create();
        }
    }

    /**
     * Dialog to send a twitter direct message
     */
    /*private static class SendDMDialog extends DialogFragment {
        private String message;
        private SharedPreferences prefs;
        private String toId;
        private TextView sliderTitle;

        private SendDMDialog(String message, TextView sliderTitle) {
            this.message = message;
            this.prefs = PreferenceManager.getDefaultSharedPreferences(getActivity());
            this.sliderTitle = sliderTitle;
            this.toId = sliderTitle.getText().toString();
        }

        @Override
        public Dialog onCreateDialog(Bundle savedInstanceState) {
            final View view = getActivity().getLayoutInflater().inflate(R.layout.dialog_send_dm, null);
            final EditText messageText = (EditText) view.findViewById(R.id.sendDM);
            messageText.setText(message);
            messageText.setSelection(messageText.getText().length());

            return new AlertDialog.Builder(new ContextThemeWrapper(getActivity(), R.style.DroidHub_Light_Dialog))
                    .setTitle("Send DM")
                    .setView(view)
                    .setPositiveButton("Send", new DialogInterface.OnClickListener() {
                        public void onClick(DialogInterface dialog, int id) {
                            message = messageText.getText().toString();

                            if (message.isEmpty()) {
                                Toast.makeText(getActivity(), "Empty message", Toast.LENGTH_SHORT).show();

                                DialogFragment newFragment = new SendDMDialog(message, sliderTitle);
                                newFragment.show(getFragmentManager(), "SendDMDialog");

                                return;
                            }

                            final ProgressDialog sendStatus = new ProgressDialog(getActivity(), ProgressDialog.THEME_HOLO_LIGHT);
                            sendStatus.setMessage("Sending...");
                            sendStatus.show();

                            TextView tv = (TextView) sendStatus.findViewById(android.R.id.message);
                            tv.setTextColor(Color.parseColor("#33b5e5"));
                            tv.setTextSize(20);

                            new AsyncTask<Void, Void, String>() {
                                @Override
                                protected String doInBackground(Void... params) {
                                    Twitter twitter = new Twitter("DroidHub", new OAuthSignpostClient(Settings.TWITTER_OAUTH_KEY, Settings.TWITTER_OAUTH_SECRET,
                                            prefs.getString(Settings.TWITTER_PREF_TOKEN, null), prefs.getString(Settings.TWITTER_PREF_SECRET, null)));

                                    try {
                                        twitter.sendMessage(toId, message);
                                    } catch (TwitterException.E403 e) {
                                        return "Can't send DM: " + toId + " is not following you";
                                    } catch (TwitterException e) {
                                        return "Error";
                                    }

                                    return "DM Sent";
                                }

                                @Override
                                protected void onPostExecute(String result) {
                                    super.onPostExecute(result);

                                    sendStatus.dismiss();
                                    Toast.makeText(getActivity(), result, Toast.LENGTH_SHORT).show();

                                    if (!result.equals("DM Sent")) {
                                        DialogFragment newFragment = new SendDMDialog(message, sliderTitle);
                                        newFragment.show(getFragmentManager(), "SendDMDialog");
                                    }
                                }
                            }.execute();
                        }
                    })
                    .setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
                        public void onClick(DialogInterface dialog, int id) {
                        }
                    })
                    .create();
        }
    }*/

    /**
     * Dialog to send a tweet
     */
    /*private static class SendMNDialog extends DialogFragment {
        private String message;
        private SharedPreferences prefs;
        private String sendTo;
        private String sendToId;

        private SendMNDialog(String message, String sendTo, String sendToId) {
            this.message = message;
            this.prefs = PreferenceManager.getDefaultSharedPreferences(getActivity());
            this.sendTo = sendTo;
            this.sendToId = sendToId;
        }

        @Override
        public Dialog onCreateDialog(Bundle savedInstanceState) {
            final View view = getActivity().getLayoutInflater().inflate(R.layout.dialog_send_dm, null);
            final EditText messageText = (EditText) view.findViewById(R.id.sendDM);
            messageText.setText(message != null ? message : sendTo + " ");
            messageText.setSelection(messageText.getText().length());

            return new AlertDialog.Builder(new ContextThemeWrapper(getActivity(), R.style.DroidHub_Light_Dialog))
                    .setTitle("Tweet")
                    .setView(view)
                    .setPositiveButton("Send", new DialogInterface.OnClickListener() {
                        public void onClick(DialogInterface dialog, int id) {
                            message = messageText.getText().toString();

                            if (message.isEmpty()) {
                                Toast.makeText(getActivity(), "Empty tweet", Toast.LENGTH_SHORT).show();

                                DialogFragment newFragment = new SendMNDialog(message, sendTo, sendToId);
                                newFragment.show(getFragmentManager(), "SendMNDialog");

                                return;
                            }

                            final ProgressDialog sendStatus = new ProgressDialog(getActivity(), ProgressDialog.THEME_HOLO_LIGHT);
                            sendStatus.setMessage("Sending...");
                            sendStatus.show();

                            TextView tv = (TextView) sendStatus.findViewById(android.R.id.message);
                            tv.setTextColor(Color.parseColor("#33b5e5"));
                            tv.setTextSize(20);

                            new AsyncTask<Void, Void, String>() {
                                @Override
                                protected String doInBackground(Void... params) {
                                    Twitter twitter = new Twitter("DroidHub", new OAuthSignpostClient(Settings.TWITTER_OAUTH_KEY, Settings.TWITTER_OAUTH_SECRET,
                                            prefs.getString(Settings.TWITTER_PREF_TOKEN, null), prefs.getString(Settings.TWITTER_PREF_SECRET, null)));

                                    try {
                                        twitter.updateStatus(message, new BigInteger(sendToId));
                                    } catch (TwitterException e) {
                                        return "Error";
                                    }

                                    return "Tweet sent";
                                }

                                @Override
                                protected void onPostExecute(String result) {
                                    super.onPostExecute(result);

                                    sendStatus.dismiss();
                                    Toast.makeText(getActivity(), result, Toast.LENGTH_SHORT).show();

                                    if (!result.equals("Tweet sent")) {
                                        DialogFragment newFragment = new SendMNDialog(message, sendTo, sendToId);
                                        newFragment.show(getFragmentManager(), "SendMNDialog");
                                    }
                                }
                            }.execute();
                        }
                    })
                    .setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
                        public void onClick(DialogInterface dialog, int id) {
                        }
                    })
                    .create();
        }
    }*/
}