package com.aluxian.apps.droidhub.utilitaries;

import java.util.ArrayList;
import java.util.List;

/**
 * Source: https://gist.github.com/fluxtah/2559252
 */
public class SelectionQueryBuilder {
    public interface Op {
        public String EQ = " = ";
        public String NEQ = " != ";
        public String GT = " > ";
        public String LT = " < ";
        public String GTEQ = " >= ";
        public String LTEQ = " <= ";
        public String LIKE = " LIKE ";
        public String IS = " IS ";
        public String ISNOT = " IS NOT ";
        public String REGEXP = " REGEXP ";
    }

    private static final String AND = " AND ";
    private static final String OR = " OR ";

    private StringBuilder mBuilder;
    private List<String> mArgs = new ArrayList<String>();

    public SelectionQueryBuilder() {
        mBuilder = new StringBuilder();
    }

    public List<String> getArgs() {
        return mArgs;
    }

    public String[] getArgsArray() {
        return mArgs.toArray(new String[mArgs.size()]);
    }

    public String getQuery() {
        return mBuilder.toString();
    }

    public SelectionQueryBuilder and(SelectionQueryBuilder builder) {
        if (mBuilder.length() > 0) {
            mBuilder.append(AND);
        }

        mBuilder.append("(").append(builder).append(")");
        mArgs.addAll(builder.getArgs());

        return this;
    }

    public SelectionQueryBuilder or(SelectionQueryBuilder builder) {
        if (mBuilder.length() > 0) {
            mBuilder.append(OR);
        }

        mBuilder.append("(").append(builder).append(")");
        mArgs.addAll(builder.getArgs());

        return this;
    }

    public SelectionQueryBuilder and(String column, String op, Object arg) {
        if (mBuilder.length() > 0) {
            mBuilder.append(AND);
        }

        mBuilder.append(column).append(op).append("?");
        mArgs.add(String.valueOf(arg));

        return this;
    }

    public SelectionQueryBuilder or(String column, String op, Object arg) {
        if (mBuilder.length() > 0) {
            mBuilder.append(OR);
        }

        mBuilder.append(column).append(op).append("?");
        mArgs.add(String.valueOf(arg));

        return this;
    }
}