using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class EnemyPlaneMove : MonoBehaviour
{
    public float actualRotation;
    public bool inmunity;
    public int pos;
    public float speed;

    // Start is called before the first frame update
    void Start()
    {

        if (transform.position.x > 1 && transform.position.z < 18 && transform.position.z > -9)
        { RotatePlane(-90); }

        else if (transform.position.x < 1 && transform.position.z < 18 && transform.position.z > -9)
        { RotatePlane(90); }

        else if (transform.position.z > 1)
        { RotatePlane(180); }

        else if (transform.position.z < 1)
        { RotatePlane(0); }
    }

    void Update()
    {
        if (transform.position.x > 50 || transform.position.x < -50 || transform.position.z < -30 || transform.position.z > 40) { Destroy(gameObject); }
            transform.Translate(Vector3.forward * Time.deltaTime * speed);
    }

    private void RotatePlane(float i)
    {
        actualRotation = i;
        transform.localRotation = Quaternion.Euler(0, i, 0);
    }

}
