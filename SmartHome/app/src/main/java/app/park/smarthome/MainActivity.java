package app.park.smarthome;

import android.Manifest;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.support.annotation.RequiresApi;
import android.support.v7.app.AppCompatActivity;
import android.telephony.TelephonyManager;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.google.firebase.iid.FirebaseInstanceId;

public class MainActivity extends AppCompatActivity {
    SharedPreferences host;
    String ServerHost;

    @RequiresApi(api = Build.VERSION_CODES.M)
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);
        Intent intent = getIntent();
        Bundle bundle = intent.getExtras();
        if(bundle != null){
            if(bundle.getString("ServerHost") != null && !bundle.getString("ServerHost").equalsIgnoreCase("")){
                Intent web = new Intent(Intent.ACTION_VIEW, Uri.parse("http://"+bundle.getString("ServerHost")));
                startActivity(web);
            }
        }
        chkPermission();
        String token = FirebaseInstanceId.getInstance().getToken();
        //Toast.makeText(getApplicationContext(),token, Toast.LENGTH_SHORT).show();

        host = getSharedPreferences("one",0);
        ServerHost = host.getString("host","");
        Button Send = (Button)findViewById(R.id.Send);
        final EditText HostIp = (EditText)findViewById(R.id.HostIp);

        HostIp.setText(ServerHost);
        Send.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View view){
                if(HostIp.getText().toString().trim().length()>11) {
                    ServerHost = HostIp.getText().toString().trim();
                    //Toast.makeText(getBaseContext(),ServerHost,Toast.LENGTH_SHORT).show();

                    String token = FirebaseInstanceId.getInstance().getToken();
                    TelephonyManager telephonyManager = (TelephonyManager) getSystemService(getApplicationContext().TELEPHONY_SERVICE);
                    String PhoneNumber = telephonyManager.getLine1Number();

                    new KeyRegist().execute(ServerHost,token,PhoneNumber);

                    host = getSharedPreferences("one", 0);
                    SharedPreferences.Editor editor = host.edit();
                    editor.putString("host", ServerHost);
                    editor.commit();
                    Toast.makeText(getApplicationContext(),"서버연결 성공", Toast.LENGTH_SHORT).show();
                }
                else{
                    Toast.makeText(getApplicationContext(),"연결 실패 서버를 확인해주세요", Toast.LENGTH_SHORT).show();
                    HostIp.setText("");
                }
            }
        });
    }

    @Override
    protected void onPause() {
        super.onPause();

        host = getSharedPreferences("one", 0);
        SharedPreferences.Editor editor = host.edit();
        editor.putString("host", ServerHost);
        editor.commit();
    }

    @RequiresApi(api = Build.VERSION_CODES.M)
    private void chkPermission(){
        if(checkSelfPermission(Manifest.permission.READ_PHONE_STATE) != PackageManager.PERMISSION_GRANTED) {
            //권한이 없을때 최초 실행시 작동안함(false)
            //사용자가 권한요청을 한 번 거절하면 다음실행부터는 shouldShowRequestPermissionRationale는 true를 반환하여 작동
            if(shouldShowRequestPermissionRationale(Manifest.permission.READ_PHONE_STATE)){
                Toast.makeText(this, "앱 실행을 위해서는 권한을 설정해야 합니다.", Toast.LENGTH_SHORT).show();
            }

            //권한요청 팝업창
            requestPermissions(new String[]{Manifest.permission.READ_PHONE_STATE}, 1);
        }
    }
    @RequiresApi(api = Build.VERSION_CODES.M)
    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        switch (requestCode) {
            case 1:
                if (grantResults[0] != PackageManager.PERMISSION_GRANTED) {
                    chkPermission();
                }
                break;
        }
    }
}
