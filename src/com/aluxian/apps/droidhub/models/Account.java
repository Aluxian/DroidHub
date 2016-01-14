package com.aluxian.apps.droidhub.models;

import org.json.JSONObject;

/**
 * Account object
 */
public class Account {
    public String id;
    public Item.Type type;
    public JSONObject credentials;

    public Account(Item.Type type, JSONObject credentials) {
        this(null, type, credentials);
    }

    public Account(String id, Item.Type type, JSONObject credentials) {
        this.id = id;
        this.type = type;
        this.credentials = credentials;
    }
}