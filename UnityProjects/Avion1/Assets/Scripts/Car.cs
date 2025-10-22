using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class Car : MonoBehaviour
{
    public float speed;
    public float turningSpeed;
    public GameObject[] carPrefabs;
    private Rigidbody rb;
    RaycastHit carInfront;
    int layerMask = 1 << 30;
    public bool canMove;
    public bool seguir;
    public bool giro;
    public bool touchingNode;
    public bool moveR;
    public bool moveL;
    public bool moveRF;
    public bool moveLF;
    public bool readyToRead;
    public bool readyToSeeD;
    public bool readyToSeeI;
    public bool readyToSeeDA;
    public int rayDistance;
    public float actualRotation;


    void Start()
    {
        readyToSeeD = false;
        readyToSeeD = false;
        readyToSeeI = false;
        canMove = true;
        Instantiate(SelectACarPrefab(), transform);
        rb = gameObject.GetComponent<Rigidbody>();
        

        RotateCar(0) ;

        if (transform.position.x > 34)
        { RotateCar(-90); /*i = -5;*/ }

        else if (transform.position.x < -34)
        { RotateCar(90);  /*i = 5;*/ }

        else if (transform.position.z > 29)
        { RotateCar(180); }

        else if (transform.position.z < -11)
        { RotateCar(0);; }
    }

    void Update()
    {
        if (canMove == true)
        {
            
            transform.Translate(Vector3.forward * Time.deltaTime * speed);

            if(moveR == true)
            {
                GiroDcha();
            }
            else if (moveL == true)
            {
                GiroIda();
            }
            else if (moveRF == true)
            {
                GiroDchaA();
            }

        }
        else { };
        if (Physics.Raycast(transform.position, transform.TransformDirection(Vector3.forward), out carInfront, rayDistance, layerMask))
        {
            Debug.DrawRay(transform.position, transform.TransformDirection(Vector3.forward) * 20000, Color.red);
            Debug.Log("Car Infront");
            canMove = false;
        }
        else
        {
            canMove = true;
            Debug.DrawRay(transform.position, transform.TransformDirection(Vector3.forward) * 20000, Color.green);
        }

        if (transform.position.x >50 || transform.position.x < -50 || transform.position.z > 40 || transform.position.z < -20)
        {
            Destroy(gameObject);
        }
    }

    private GameObject SelectACarPrefab()
    {
        var randomIndex = Random.Range(0, carPrefabs.Length);
        return carPrefabs[randomIndex];
    }

    private void RotateCar(float i)
    {
        actualRotation = i;   
        transform.localRotation = Quaternion.Euler(0, i, 0);
    }

    private void GiroDcha()
    {
        if (canMove == true)
        {
            speed = 4f;

            transform.Rotate(Vector3.up * turningSpeed * Time.deltaTime);
            if (readyToSeeD == false)
            {
                StartCoroutine(TimerD());
            }
            else if (transform.rotation.eulerAngles[1] % 90 < 0.5 || transform.rotation.eulerAngles[1] % 90 > 89.5)
            {
                touchingNode = false;
                readyToSeeD = false;
                moveR = false;
                moveRF = false;
                giro = false;
                speed = 6;

            }
        }
    }
    private void GiroIda()
    {
        if (canMove == true)
        {
            speed = 4f;

            transform.Rotate(Vector3.up * turningSpeed * Time.deltaTime * -1);
            if (readyToSeeI == false)
            {
                StartCoroutine(TimerI());
            }
            else if (transform.rotation.eulerAngles[1] % 90 < 0.5 || transform.rotation.eulerAngles[1] % 90 > 89.5)
            {
                touchingNode = false;
                readyToSeeI = false;
                moveL = false;
                speed = 6;

            }
        }
    }
    private void GiroDchaA()
    {
        if (!giro && !seguir)
        {
            if (canMove == true)
            {
                switch (Random.Range(1, 3))
                {
                    case 1:
                        giro = true;
                        break;

                    case 2:
                        seguir = true;
                        break;
                }
            }
        }
        else if (giro)
        {
            GiroDcha();
        }
        else if (seguir)
        {
            StartCoroutine(TimerSigo());
        }
    }


    IEnumerator TimerD()
    {
        yield return new WaitForSeconds(1f);
        readyToSeeD = true;
    }

    IEnumerator TimerI()
    {
        yield return new WaitForSeconds(1f);
        readyToSeeI = true;
    }
    IEnumerator TimerDA()
    {
        yield return new WaitForSeconds(1.5f);
        giro = false;
    }
        IEnumerator TimerSigo()
        {
            yield return new WaitForSeconds(1.5f);
            seguir = false;
            moveRF = false;
        }

        private void OnTriggerEnter(Collider other)
    {
        if (other.gameObject.tag == "nodeR")
        {
            touchingNode = true;
            moveR = true;
        }
        else if (other.gameObject.tag == "nodeI")
        {
            touchingNode = true;
            moveL = true;
        }
        if (other.gameObject.tag == "nodeRF")
        {
            touchingNode = true;
            moveRF = true;
        }
    }
}
