package app.park.smarthome;

import android.content.SharedPreferences;

import com.google.firebase.iid.FirebaseInstanceId;
import com.google.firebase.iid.FirebaseInstanceIdService;

import java.io.IOException;

import okhttp3.FormBody;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
/**
 * Created by 박재호 on 2017-08-18.
 */

public class IDService extends FirebaseInstanceIdService {
    String ServerHost;
    SharedPreferences host;

    @Override
    public void onTokenRefresh() {
        host = getSharedPreferences("one", 0);
        ServerHost = host.getString("host", "");
        String token = FirebaseInstanceId.getInstance().getToken();
        OkHttpClient client = new OkHttpClient();
        RequestBody body = new FormBody.Builder().add("Token", token).build();
        Request request = new Request.Builder().url(ServerHost+"/register.php").post(body).build();

        try {
            client.newCall(request).execute();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
