package CampusMap;

import java.lang.reflect.Method;
import java.lang.reflect.InvocationTargetException;

/** Class to store a method that can be invoked at a certain time in Camera
 *
 */

public class CameraAction {
	
	private Camera		theCamera;
	private Method		cameraMethod;
	private Object[]	parameters;
	private int			startTime = 0;
	private int			waitTime;
	
	public CameraAction(Camera p_theCamera, Method p_cameraMethod, int p_waitTime, Object[] p_parameters) {
		theCamera = p_theCamera;
		cameraMethod = p_cameraMethod;
		waitTime = p_waitTime;
		parameters = p_parameters;
	}
	               
	public boolean check(int currentTime) {
		if (startTime == 0) {
			startTime = currentTime + waitTime;
		}
		else if (currentTime > startTime) {
			try {
				cameraMethod.invoke(theCamera, parameters);
			} catch (InvocationTargetException e) {
				System.out.println("CameraAction: " + e);
			} catch (IllegalAccessException e) {
				System.out.println("CameraAction: " + e);
			}
			return true;
		}
		return false;
	}
	
}