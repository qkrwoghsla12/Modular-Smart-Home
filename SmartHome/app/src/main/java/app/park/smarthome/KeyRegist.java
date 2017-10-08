package app.park.smarthome;

import android.os.AsyncTask;

import okhttp3.FormBody;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;

/**
 * Created by 박재호 on 2017-08-18.
 */

public class KeyRegist extends AsyncTask<String,Integer,Boolean> {
    protected void onPreExecute(){
    }

    @Override
    protected Boolean doInBackground(String... value) {
        String ServerHost = value[0];

        OkHttpClient client = new OkHttpClient();
        if(value[2] == null){
            value[2] = "0";
        }
        RequestBody body = new FormBody.Builder().add("Token",value[1]).add("PhoneNum",value[2]).build();
        Request request = new Request.Builder().url("http://"+ServerHost+"/register.php").post(body).build();
        try {
            client.newCall(request).execute();
        } catch (Exception e) {
            e.printStackTrace();
        }
        return null;
    }
    protected void onPostExecute(Integer result) {
    }
};

