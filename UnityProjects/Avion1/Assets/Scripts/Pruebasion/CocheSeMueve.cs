using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class CocheSeMueve : MonoBehaviour
{
    public float speed;
    private float horizontalInput;
    private float forwardInput;

    private void Update(){
        if (Input.GetKey("space"))
           {GiroMistico();}
    }

    private void FixedUpdate(){
        if (transform.rotation.eulerAngles[1] % 90 < 1)
        {
            Debug.Log("YA baby");
        }
    }
     void GiroMistico(){
        transform.Rotate(Vector3.up * speed * Time.deltaTime);

    }
}
